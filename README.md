# FS_GoCuotas for Magento 2

This Extension is used to make payments using Go Cuotas API in Argentina.

- Allow end user set credentials, checkout message and name of payment method 
- Generate pending orders and using return parameters back and change to order status cancel or processing (and create invoice).
- Add notification url to get updates using webhook_url
- cancel order and regenerate a new one and redirect to checkout on failing payment.

## Manual Installation

- Create a folder [root]/app/code/FS/GoCuotas
- Download module ZIP
- Copy to folder
- test credentials: 
username:seller_sandbox@gocuotas.com
password:secret

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
