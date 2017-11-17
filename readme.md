# MyOngkir Beta (WooCommerce Shipping Method Add-on)

Please note, this is still beta.

This is additional shipping methods for indonesian wordpress based e-commerce. This plugin using [rajaongkir.com](https://rajaongkir.com) API.

## Requirements

- Wordpress 4.9
- woocommerce 3.2.4

## Change Log

### 20-12-2015

- Change base url
- Added couriers options

### 08-04-2015

- Implement select2.js
- fix handling Rajaongkir API result

## How to Install

I assume You already have woocommerce plugin installed.

- Install this add-on plugin.
- Create rajaongkir.com account [here](http://rajaongkir.com/akun/daftar) for API key.
- When you get the API key, go to WooCommerce menu then hit `General` tab, find and fill `RajaOngkir API Key` field with your API key
- Go to `Shipping` tab, and `myongkir shipping` section to choose your base city.
> Note: before you choose the city base, ensure that you have set the woocommerce base country to indonesian state/province, e.g: Indonesia-DKI Jakarta.

- Check `Enable this shipping method` option.
- Congrats! Now you can ship your products using various indonesian couriers!

## Todos

- Add proper shipping calculation on Cart page.
- Order's minimum weight.
- Weight validation on add product, don't save product if weight is empty

## Notes
- Please put weight for every product. It will affect the shipping cost.


## Lisence
MIT License &copy; 2017 Muhammad Rizki Rijal
