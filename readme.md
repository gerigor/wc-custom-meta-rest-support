# WC Custom Meta REST Support
A quick-and-dirty WordPress plugin that allows you to register custom WooCommerce product meta fields for use with the REST API.

## Usage
- Install and activate the plugin.
- Go to WooCommerce > REST Meta Fields.
- Add your custom meta keys, select type, and hit Save Fields.
- Use WooCommerce REST API to update or retrieve those fields.

## Note: 
Probably, the "Single?" checkbox doesn’t retain its state after saving, will fix it later.

### To update a registered field via REST:
```
PUT /wp-json/wc/v3/products/<product_id>
{
  "meta_data": [
    {
      "key": "your_custom_meta_key",
      "value": "your value"
    }
  ]
}
```
