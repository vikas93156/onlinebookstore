<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
   header('location:login.php');
}

if (isset($_POST['order_btn'])) {

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $number = $_POST['number'];
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $method = mysqli_real_escape_string($conn, $_POST['method']);
   $address = mysqli_real_escape_string($conn, 'flat no. ' . $_POST['flat'] . ', ' . $_POST['street'] . ', ' . $_POST['city'] . ', ' . $_POST['country'] . ' - ' . $_POST['pin_code']);
   $placed_on = date('d-M-Y');

   if (strlen($number) != 10 || !ctype_digit($number)) {
      $message[] = 'Please enter a valid 10-digit phone number.';
   } else {
      $cart_total = 0;
      $cart_products = [];

      $cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
      if (mysqli_num_rows($cart_query) > 0) {
         while ($cart_item = mysqli_fetch_assoc($cart_query)) {
            $cart_products[] = $cart_item['name'] . ' (' . $cart_item['quantity'] . ') ';
            $sub_total = ($cart_item['price'] * $cart_item['quantity']);
            $cart_total += $sub_total;
         }
      }

      $total_products = implode(', ', $cart_products);

      $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE name = '$name' AND number = '$number' AND email = '$email' AND method = '$method' AND address = '$address' AND total_products = '$total_products' AND total_price = '$cart_total'") or die('query failed');

      if ($cart_total == 0) {
         $message[] = 'your cart is empty';
      } else {
         if (mysqli_num_rows($order_query) > 0) {
            $message[] = 'order already placed!';
         } else {
            mysqli_query($conn, "INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, placed_on) VALUES('$user_id', '$name', '$number', '$email', '$method', '$address', '$total_products', '$cart_total', '$placed_on')") or die('query failed');
            $message[] = 'order placed successfully!';
            mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
         }
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>checkout</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
   <style>
      .card {
         position: relative;
         display: flex;
         flex-direction: column;
         min-width: 0;
         word-wrap: break-word;
         background-color: #fff;
         background-clip: border-box;
         border: 1px solid rgba(0, 0, 0, .125);
         border-radius: .25rem;
      }
   </style>

</head>

<body>

   <?php include 'header.php'; ?>

   <div class="heading">
      <h3>checkout</h3>
      <p> <a href="home.php">home</a> / checkout </p>
   </div>

   <section class="display-order">

      <?php
      $grand_total = 0;
      $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
      if (mysqli_num_rows($select_cart) > 0) {
         while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
            $total_price = ($fetch_cart['price'] * $fetch_cart['quantity']);
            $grand_total += $total_price;
      ?>
            <p> <?php echo $fetch_cart['name']; ?> <span>(<?php echo '$' . $fetch_cart['price'] . '/-' . ' x ' . $fetch_cart['quantity']; ?>)</span> </p>
      <?php
         }
      } else {
         echo '<p class="empty">your cart is empty</p>';
      }
      ?>
      <div class="grand-total"> grand total : <span>₹<?php echo $grand_total; ?>/-</span> </div>

   </section>
   <!-- <section class="checkout">
      <form action="" method="post" id="checkoutForm">
         <h3>Place Your Order</h3>
         <div class="flex">
            <div class="inputBox">
               <span>Your Name:</span>
               <input type="text" name="name" required placeholder="Enter Your Name">
            </div>
            <div class="inputBox">
               <span>Your Number:</span>
               <input type="text" name="number" required placeholder="Enter Your Number" pattern="\d{10}" maxlength="10" title="Please enter exactly 10 digits" onkeypress="return /[0-9]/i.test(event.key)">
            </div>
            <div class="inputBox">
               <span>Your Email:</span>
               <input type="email" name="email" required placeholder="Enter Your Email">
            </div>
            <div class="inputBox">
               <span>Payment Method:</span>
               <select name="method" id="paymentMethod">
                  <option value="cash on delivery">Cash on Delivery</option>
                  <option value="online">Online</option>
               </select>
            </div>
            <div class="inputBox">
               <span>address line 01 :</span>
               <input type="number" min="0" name="flat" required placeholder="e.g. flat no.">
            </div>
            <div class="inputBox">
               <span>address line 01 :</span>
               <input type="text" name="street" required placeholder="e.g. street name">
            </div>
            <div class="inputBox">
               <span>city :</span>
               <input type="text" name="city" required placeholder="e.g. mumbai">
            </div>
            <div class="inputBox">
               <span>state :</span>
               <input type="text" name="state" required placeholder="e.g. maharashtra">
            </div>
            <div class="inputBox">
               <span>country :</span>
               <input type="text" name="country" required placeholder="e.g. india">
            </div>
            <div class="inputBox">
               <span>pin code :</span>
               <input type="number" min="0" name="pin_code" required placeholder="e.g. 123456">
            </div>
            <div id="cardDetails" style="display: none;">
               <!-- Payment Card Details -->
               <!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout Form</title>
   <style>
      .inputBox {
         margin-bottom: 15px;
      }
   </style>
</head>
<body>
   <section class="checkout">
      <form action="" method="post" id="checkoutForm">
         <h3>Place Your Order</h3>
         <div class="flex">
            <div class="inputBox">
               <span>Your Name:</span>
               <input type="text" name="name" required placeholder="Enter Your Name">
            </div>
            <div class="inputBox">
               <span>Your Number:</span>
               <input type="text" name="number" required placeholder="Enter Your Number" pattern="\d{10}" maxlength="10" title="Please enter exactly 10 digits" onkeypress="return /[0-9]/i.test(event.key)">
            </div>
            <div class="inputBox">
               <span>Your Email:</span>
               <input type="email" name="email" required placeholder="Enter Your Email">
            </div>
            <div class="inputBox">
               <span>Payment Method:</span>
               <select name="method" id="paymentMethod">
                  <option value="cash on delivery">Cash on Delivery</option>
                  <option value="online">Online</option>
               </select>
            </div>
            <div class="inputBox">
               <span>Address Line 01:</span>
               <input type="number" min="0" name="flat" required placeholder="e.g. flat no.">
            </div>
            <div class="inputBox">
               <span>Address Line 02:</span>
               <input type="text" name="street" required placeholder="e.g. street name">
            </div>
            <div class="inputBox">
               <span>City:</span>
               <input type="text" name="city" required placeholder="e.g. Mumbai">
            </div>
            <div class="inputBox">
               <span>State:</span>
               <input type="text" name="state" required placeholder="e.g. Maharashtra">
            </div>
            <div class="inputBox">
               <span>Country:</span>
               <input type="text" name="country" required placeholder="e.g. India">
            </div>
            <div class="inputBox">
               <span>Pin Code:</span>
               <input type="number" min="0" name="pin_code" required placeholder="e.g. 123456">
            </div>
            <div id="cardDetails" style="display: none;">
               <!-- Payment Card Details -->
               <div class="inputBox">
                  <span>Card Number:</span>
                  <input type="text" name="card_number" placeholder="Enter Card Number" pattern="\d{16}" title="Please enter exactly 16 digits" maxlength="16">
               </div>
               <div class="inputBox">
                  <span>Expiry Date:</span>
                  <input type="text" name="expiry_date" placeholder="MM/YYYY" pattern="\d{2}/\d{4}" title="Please enter in MM/YYYY format">
               </div>
               <div class="inputBox">
                  <span>CVV:</span>
                  <input type="text" name="cvv" placeholder="CVV" pattern="\d{3}" title="Please enter exactly 3 digits" maxlength="3">
               </div>
               <div class="inputBox">
                  <span>Amount:</span>
                  <input type="number" min="0" name="amount" placeholder="Enter Amount">
               </div>
            </div>
         </div>
         <script>
            document.getElementById('paymentMethod').addEventListener('change', function() {
               var cardDetails = document.getElementById('cardDetails');
               if (this.value === 'online') {
                  cardDetails.style.display = 'block';
                  // Making card fields required if online payment is selected
                  document.querySelectorAll('#cardDetails input').forEach(function(input) {
                     input.required = true;
                  });
               } else {
                  cardDetails.style.display = 'none';
                  // Removing required attribute from card fields if online payment is not selected
                  document.querySelectorAll('#cardDetails input').forEach(function(input) {
                     input.required = false;
                  });
               }
            });
         </script>
         <input type="submit" value="Order Now" class="btn" name="order_btn">
      </form>
   </section>
</body>
</html>

               <div class="inputBox">
                  <span>Card Number:</span>
                  <input type="text" name="card_number" required placeholder="Enter Card Number">
               </div>
               <div class="inputBox">
                  <span>Expiry Date:</span>
                  <input type="text" name="expiry_date" required placeholder="MM/YYYY">
               </div>
               <div class="inputBox">
                  <span>CVV:</span>
                  <input type="text" name="cvv" required placeholder="CVV">
               </div>
               <div class="inputBox">
                  <span>Amount:</span>
                  <input type="number" min="0" name="amount" required placeholder="Enter Amount">
               </div>
            </div>
         </div>

         <script>
            document.getElementById('paymentMethod').addEventListener('change', function() {
               var cardDetails = document.getElementById('cardDetails');
               if (this.value === 'online') {
                  cardDetails.style.display = 'block';
               } else {
                  cardDetails.style.display = 'none';
               }
            });
         </script>
         </div>
         <input type="submit" value="order now" class="btn" name="order_btn">
      </form>
   </section> -->

   <?php include 'footer.php'; ?>

   <!-- custom js file link  -->
   <script src="js/script.js"></script>

</body>

</html>