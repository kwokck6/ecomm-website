  <script src="https://www.paypal.com/sdk/js?currency=HKD&client-id=<?php echo json_decode(file_get_contents('../secret.json'))->client_id; ?>"></script>

  <!-- Set up a container element for the button -->
  <div id="paypal-button-container"></div>

  <script>
    paypal.Buttons({
      /* Sets up the transaction when a payment button is clicked */
      createOrder: async (data, actions) => {
        /* [TODO] create an order from localStorage */
        let order_details = await fetch("create_order.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(getCartItems(), null, 2)
        }).then(response => response.json());

        console.log(order_details);

        return actions.order.create(order_details);
      },

      /* Finalize the transaction after payer approval */
      onApprove: async (data, actions) => {
        return actions.order.capture()
          .then(async orderData => {
            /* Successful capture! For dev/demo purposes: */
            console.log('Capture result', orderData, JSON.stringify(orderData, null, 2));

            await fetch('save_order.php', {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
              },
              body: JSON.stringify(orderData, null, 2)
            });

            clearCart(); // Clear the web shop cart
            window.location.href = "index.php"; // Redirect to another page
          });
      },
    }).render('#paypal-button-container');
  </script>
