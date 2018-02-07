<?php
/**
 * Created by PhpStorm.
 * User: Mike Finch
 * Date: 07.02.2018
 * Time: 11:26
 *
 *
 */

namespace mikefinch\YandexKassaAPI\actions;

use mikefinch\YandexKassaAPI\YandexKassaAPI;
use yii\base\Action;
use yii\web\HttpException;
use mikefinch\YandexKassaAPI\interfaces\OrderInterface;

class CreatePaymentAction extends Action {

    /**
     * Выполняется перед платежом.
     * @var callable
     */
    public $beforePayment;
    public $componentName = "kassa";

    /**
     * @var string
     */
    public $orderClass;

    public function run($id) {

        $orderModel = \Yii::createObject($this->orderClass);

        if (!$orderModel instanceof OrderInterface) {
            throw new HttpException(500, "Модель должна реализовывать интерфейс OrderInterface");
        }

        $order = $orderModel->findById($id);

        if ($this->beforePayment && !call_user_func($this->beforePayment, $order)) {
            throw new HttpException(500, "Произошла ошибка исполнения платежа");
        }

        $payment = $this->getComponent()->createPayment($order);
        return $this->controller->redirect($payment->confirmation->getConfirmationUrl());

    }

    /**
     * @return YandexKassaAPI;
     */
    private function getComponent() {
        return \Yii::$app->get($this->componentName);
    }

}