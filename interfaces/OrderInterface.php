<?php
/**
 * Created by PhpStorm.
 * User: Mike Finch
 * Date: 07.02.2018
 * Time: 10:47
 */

namespace mikefinch\YandexKassaAPI\interfaces;

use yii\db\ActiveRecordInterface;

interface OrderInterface extends ActiveRecordInterface {

    /**
     * @param $invoiceId string
     */
    public function setInvoiceId($invoiceId);

    /**
     * @return string
     */
    public function getInvoiceId();

    /**
     * @return integer
     */
    public function getPaymentAmount();

    /**
     * @param $invoiceId
     * @return OrderInterface
     */
    public function findByInvoiceId($invoiceId);

    /**
     * @param $id
     * @return OrderInterface
     */
    public function findById($id);

}