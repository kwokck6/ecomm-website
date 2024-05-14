# IERG4210 E-Commerce Shop

Author: Kwok Chun Kiu
Date: 17 Jan 2023

## Structure
- cart.db
- secret.json
- (certificates)
- phase-5
    - index.php
    - cat-manage.php
    - admin.php
    - admin-process.php
    - product.php
    - prod-manage.php
    - payment.php
    - create_order.php
    - save_order.php
    - README.md
    - js/
        - cart.js
        - form_control.js
    - admin/
        - db.inc.php
        - db.php
        - auth.php
        - user.inc.php
    - css/
        - index.css
        - product.css
        - admin.css
    - img/
        
img contains the product images.
admin contains the backend scripts.

## Description
This page is programmed using plain HTML and CSS. No prior configuration or installation are required.

The webpage now contains a header, a sidebar showing the categories, the main content frame, and a footer.

### Shopping Cart
By hovering on the yellow "Cart" button, the website will display the products bought currently. Everything is hard coded for now; therefore, the subtotal will not change even the quantity is changed.

### Category side bar
The side bar displays the categories. By hovering on the categories, the website will respond by changing the background color to facilitate accessibility.

### Home page (index.php)
The home page displays the product list.

### Product page (product.php)
The product page shows the information of the product.

### Admin page (admin.php)
The admin page displays the operations for managing categories and products. Admin can also check all orders.

### My Account page (my-account.php)
The "My account page" allows the user to change passwords and check their most recent orders.