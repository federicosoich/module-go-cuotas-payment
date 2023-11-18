# FS_GoCuotas for Magento 2

This Extension is used to make payments using Go Cuotas API in Argentina.

- Allow end user set credentials, checkout message and name of payment method 
- Generate pending orders and using return parameters back and change to order status cancel or processing (and create invoice).
- Add notification url to get updates using webhook_url
- cancel order and regenerate a new one and redirect to checkout on failing payment.
- configure for send invoice by email on payment success
- configure redirect or modal onsite checkout

## Installation

- Create a folder [root]/app/code/FS/GoCuotas
- Download module ZIP from <a href="https://github.com/federicosoich/module-go-cuotas-payment/archive/refs/heads/master.zip">HERE</a>
- Copy to folder

OR

- composer require ff-ss/go-cuotas-payment

THEN
- test credentials:<br> 
username:seller_sandbox@gocuotas.com<br>
password:secret

- Documentation: https://www.gocuotas.com/api_redirect_docs
- Note: if your place order button is broke please try to remove from lines 23 to 30 of this file<br>
/view/frontend/web/template/payment/gocuotas.html 

Then you'll need to activate the module.

```
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:clean
bin/magento cache:flush
```

## Uninstall

```
bin/magento module:uninstall FS_GoCuotas
```

## Support

No warranty or support provided.

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

### How to create a PR

1. Fork it
2. Create your feature branch (git checkout -b my-new-feature)
3. Commit your changes (git commit -am 'Add some feature')
4. Push to the branch (git push origin my-new-feature)
5. Create new Pull Request

## License

[MIT](https://choosealicense.com/licenses/mit/)
