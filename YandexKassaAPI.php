<?php

namespace mikefinch\YandexKassaAPI;

use YandexCheckout\Client;
use mikefinch\YandexKassaAPI\interfaces\OrderInterface;
use yii\base\Component;


class YandexKassaAPI  extends Component {

    public $shopId;
    public $key;
    private $client;
    public $returnUrl;
    public $currency = "RUB";

    public function init() {
        parent::init();
        $this->client = new Client();
        $this->client->setAuth($this->shopId, $this->key);
    }

    public function createPayment(OrderInterface $order) {

        $payment = $this->client->createPayment(
            array(
                'amount' => array(
                    'value' => $order->getPaymentAmount(),
                    'currency' => $this->currency
                ),
                'confirmation' => array(
                    'type' => 'redirect',
                    'return_url' => $this->returnUrl,
                ),
            ),
            uniqid('', true)
        );

        $order->setInvoiceId($payment->getId());
        $order->save();

        return $payment;
    }


    public function getPayment($invoiceId) {
        return $this->client->getPaymentInfo($invoiceId);
    }

    /**
     * @param $invoiceId
     * @param $order OrderInterface
     * @return bool
     */
    public function confirmPayment($invoiceId, OrderInterface $order) {
        $payment = $this->getPayment($invoiceId);

        if ($payment->getPaid()) {
            $data = [
                'amount' => [
                    'value' => $order->getPaymentAmount(),
                    'currency' => 'RUB',
                ],
            ];

            $confirm = $this->client->capturePayment($data, $order->getInvoiceId(), $this->generateIdempotent());
            return $confirm;
        }

        return false;
    }

    private function generateIdempotent() {
        return uniqid('', true);
    }

}
