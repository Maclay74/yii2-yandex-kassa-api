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

use mikefinch\YandexKassaAPI\interfaces\OrderInterface;
use mikefinch\YandexKassaAPI\YandexKassaAPI;
use yii\base\Action;
use yii\web\HttpException;

class ConfirmPaymentAction extends Action {

    /**
     * Should return true||false
     * @var callable
     */
    public $beforeConfirm;
    public $componentName = "kassa";

    /**
     * Название класса, которое имплементирует интерфейс OrderInterface
     *
     * @var string
     */
    public $orderClass;
    
    /**
     * это нужно чтобы пост от яндекса проходил
     */
    public function init() {
        parent::init();
        $this->controller->enableCsrfValidation = false;
    }

    public function run() {

        $request = json_decode(file_get_contents('php://input'));

        $orderModel = \Yii::createObject($this->orderClass);

        if (!$orderModel instanceof OrderInterface) {
            throw new HttpException(500, "Модель должна реализовывать интерфейс OrderInterface");
        }

        $order = $orderModel->findByInvoiceId($request->object->id)->one();

        if (!$request->object->paid) {
            return false;
        }

        if ($this->beforeConfirm && call_user_func_array($this->beforeConfirm, [$request, $order])) {
            $this->getComponent()->confirmPayment($request->object->id, $order);
        }
    }

    /**
     * @return YandexKassaAPI;
     */
    private function getComponent() {
        return \Yii::$app->get($this->componentName);
    }

}
