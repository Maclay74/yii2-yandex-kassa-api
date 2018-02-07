Yandex.Kassa API extension for Yii2
===================================
This extension allows you to obtain money from users by new [Yandex.Kassa's API](https://kassa.yandex.ru/docs/checkout-api/).   
It was designed to be pretty simple to use so you don't have to deep down into Yandex.Kassa's workflow.

**This extension strongly in beta, so feel free to send pull requests and fix bugs**

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist mikefinch/yii2-yandex-kassa-api "*"
```

or add

```
"mikefinch/yii2-yandex-kassa-api": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, you have to follow next few steps:

###  1. Add extension into your configuration file

```php
'components' => [
    ...
    'kassa' => [
        'class' => 'mikefinch\YandexKassaAPI\YandexKassaAPI',
        'returnUrl' => '',
        'shopId' => '',
        'key' => '',
    ],
```

**returnUrl** - User will get there after payment has got succeeded  
**shopId** - Your shop id (from Yandex)  
**key** - Your secret key (from Yandex)  
**currency** - Currency obviously (RUB by default) 

###  2. Implement OrderInterface in your Order model

```php
class Orders extends Model implements OrderInterface {
    
    ...
      
    public function setInvoiceId($invoiceId) {
        $this->invoice_id = $invoiceId;
    }

    
    public function getInvoiceId() {
        return $this->invoice_id;
    }

    
    public function getPaymentAmount() {
        return $this->amount;
    }

    
    public function findByInvoiceId($invoiceId) {
        return self::find()->where(['invoice_id' => $invoiceId]);
    }
    
    public function findById($id) {
        return self::findOne($id);
    }

}

```
###  3. Add new actions to your controller 

```php
 public function actions() {
        return [
            'create-payment' => [
                'class'=>'mikefinch\YandexKassaAPI\actions\CreatePaymentAction',
                'orderClass' => Orders::className(),
                'beforePayment' => function($order) {
                    return $order->status == Orders::STATUS_NEW;
                }
            ],
            'notify' => [
                'class'=>'mikefinch\YandexKassaAPI\actions\ConfirmPaymentAction',
                'orderClass' => Orders::className(),
                'beforeConfirm' => function($payment, $order) {
                    $order->status = Orders::STATUS_PAID; 
                    return $order->save();
                }
            ]
        ];
    }

```

Here we have two callbacks.  

**beforePayment** checks is our order okay.
You can provide some logic there and **return false** if something went wrong and you wanna cancel your payment.

The second one is **beforeConfirm**. It executes when user successfully paid your payment
 and now you have to confirm this order, send sms notification, etc. 
 Be careful, if you **return false** is this callback, payment won't be confirmed and user will receive his money back in a few hours.

###  4. Config your Yandex.Kassa notification page

![Settings page](https://yastatic.net/doccenter/images/support.yandex.ru/ru/checkout/freeze/twhOFiPLqML0235O-XsmyV9ztM8.png) 

Set your notification page Url as same as you used in your controller.  
For example, if you added your actions to SiteController, it would be https://yoursite.com/site/notify  
Don't forget about **SSL** - Yandex sends notifications only through it.


###  5. Redirect user to payment action afterwards
```php

if ($order->payment_type == $order::PAYTYPE_ONLINE) {
    return $this->redirect(['order/create-payment', 'id' =>$order->id]);
} else {
    return $this->redirect(['order/success']);
}
```

You have to pass **$id** to the action so it could find your model by **findById** method.



