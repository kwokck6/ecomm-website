/* Actions on the page */
function _createRow(pid) {
    let div = document.createElement("div");
    div.setAttribute("id", "row_" + pid);
    div.classList.add("cart-products");
    div.innerHTML = "<div class='item'></div> " + 
    "<div class='unit-price'></div>" +
    "<div class='quantity'><input type='number' value='" + localStorage.getItem(pid) + "' min='0'" +
    "aria-valuemin='0' aria-valuenow='" + localStorage.getItem(pid) +  "' onchange='onQuantityChange(this, this.value);' /></div> \
    <div class='subtotal'></div>";
    return div;
}

function loadCart() {
    let cart = document.getElementById("cart-content");
    for (let i = 1; i < localStorage.length; i++) {
        let div = _createRow(localStorage.key(i));
        cart.insertBefore(div, cart.lastElementChild);
        _getParams(localStorage.key(i), localStorage.getItem(localStorage.key(i)));
    }
}

function getPid(element) {
    // FIXME: not object oriented (violates encapsulation -- how do you know the element has grandparent elements?)
    let pid = element.parentElement.parentElement.id;
    return pid;
}

function addToCart(pid) {
    let quantity = localStorage.getItem(pid)? localStorage.getItem(pid): 0;
    quantity = Number(quantity);
    localStorage.setItem(pid, quantity + 1);  // NOTE: quantity is stored as string by default
    
    let cart = document.getElementById("cart-content");
    
    if (quantity === 0) {
        let div = _createRow(pid);
        cart.insertBefore(div, cart.lastElementChild);
    } else {
        let input = document.querySelector("#row_" + pid + " input");
        input.setAttribute("value", quantity + 1);
    }
    _getParams(pid, quantity + 1);
}

/* Actions in the shopping cart */
function onQuantityChange(element, quantity) {
    let pid = getPid(element).split('_')[1];
    _updateQuantity(pid, quantity);
    if (quantity != 0) {
        _calculateSubtotal(pid, quantity);
    }
    _calculateTotal();
}

function _updateQuantity(pid, quantity) {
    if (quantity == 0) {
        _confirmDeletion(pid);
    } else {
        _checkQuantity(pid, quantity);
    }
}

function clearCart() {
    localStorage.clear();
    let rows = document.querySelectorAll("div.cart-products");
    rows.forEach(element => {
        element.remove();
    });
}

function confirmClearCart() {
    let response = confirm("Are you sure you want to remove all products from the shopping cart?");
    if (response) {
        clearCart();
    }
}

function _confirmDeletion(pid) {
    let response = confirm("Are you sure you want to delete this item?");
    if (response) {
        localStorage.removeItem(pid);
        let row = document.getElementById("row_" + pid);
        row.remove();
    } else {
        let el_quantity = document.querySelector("#row_" + pid + " input");
        el_quantity.value = 1;
        el_quantity.setAttribute('value', 1);
        el_quantity.setAttribute('aria-valuenow', 1);
        onQuantityChange(el_quantity, 1); 
    }
}

function _checkQuantity(pid, quantity) {
    let xhr = new XMLHttpRequest();
    xhr.open('GET', './admin-process.php?action=prod_fetchOne_get&pid=' + pid, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send();
    xhr.onreadystatechange = function () {
        let done = 4;
        let ok = 200;
        if (this.readyState === done && this.status === ok) {
            let response = xhr.responseText.substring(0, 9) == "while(1);"? xhr.responseText.substring(9): {"error": "Invalid response"};
            let output = JSON.parse(response);
            let inventory = parseInt(output.success.inventory);
            let el_quantity = document.querySelector("#row_" + pid + " input");
            quantity = Number(quantity);
            if (quantity > inventory) {
                alert("The quantity you requested exceeds the number available in our inventory.");
                quantity = inventory;
                el_quantity.setAttribute("max", inventory); 
                el_quantity.setAttribute("aria-valuemax", inventory);
            } 
            localStorage.setItem(pid, quantity);
            el_quantity.setAttribute('value', quantity);
            el_quantity.setAttribute('aria-valuenow', quantity);
        }
    }
}

/* One-time functions */
function _getParams(pid, quantity) {
    let xhr = new XMLHttpRequest();
    xhr.open('GET', './admin-process.php?action=prod_fetchOne_get&pid=' + pid, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send();
    xhr.onreadystatechange = function () {
        let done = 4;
        let ok = 200;
        if (this.readyState === done && this.status === ok) {
            let response = xhr.responseText.substring(0, 9) == "while(1);"? xhr.responseText.substring(9): {"error": "Invalid response"};
            let output = JSON.parse(response);
            let pname = output.success.name;
            let price = parseFloat(output.success.price).toFixed(2);
            let inventory = parseInt(output.success.inventory);
            let el_name = document.querySelector("#row_" + pid + " .item");
            el_name.innerHTML = pname;
            let el_price = document.querySelector("#row_" + pid + " .unit-price");
            el_price.innerHTML = "$" + price;
            let el_quantity = document.querySelector("#row_" + pid + " input");
            el_quantity.setAttribute("max", inventory); 
            el_quantity.setAttribute("aria-valuemax", inventory);
            _calculateSubtotal(pid, quantity);
            _calculateTotal();
        }
    }
}

function _calculateSubtotal(pid, quantity) {
    let el_price = document.querySelector("#row_" + pid + " .unit-price");
    price = parseFloat(el_price.innerHTML.substring(1));
    quantity = parseInt(quantity);
    let el_subtotal = document.querySelector("#row_" + pid + " .subtotal");
    el_subtotal.innerHTML = "$" + (price * quantity).toFixed(2);
}

function _calculateTotal() {
    let el_total = document.querySelectorAll(".cart-products .subtotal");
    let total = parseFloat(0);
    el_total.forEach(function (element) {
        total += parseFloat(element.innerHTML.substring(1));
    });
    total;
    let p_total = document.querySelector("p#total");
    p_total.innerHTML = "Total: $" + total.toFixed(2);
}

/**
 * This function returns all items in the cart. 
 * @return an array containing items (pid + quantity)
 */
function getCartItems() {
    let cartItems = [];
    for (let i = 1; i < localStorage.length; i++) {
        cartItems.push({pid: localStorage.key(i), quantity: localStorage.getItem(localStorage.key(i))});
    }
    return cartItems;
}

