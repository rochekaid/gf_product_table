//add this to a Gravity Form HTML field
$(document).ready(function () {
  jQuery("#gform_submit_button_274").hide();
  jQuery(".tx_decrement").on("click", function () {
    var $quantity = jQuery(this).next(".tx_quantity");
    var currentValue = parseInt($quantity.text());
    if (currentValue > 0) {
      $quantity.text(currentValue - 1);
      updateSummary();
    }
  });

  jQuery(".tx_increment").on("click", function () {
    var $quantity = jQuery(this).prev(".tx_quantity");
    var currentValue = parseInt($quantity.text());
    var maxInventory = parseInt(jQuery(this).data("inventory"));

    if (currentValue < maxInventory) {
      $quantity.text(currentValue + 1);
      updateSummary();
    } else {
      alert("You have reached the maximum inventory quantity.");
    }
  });

  function updateSummary() {
    let totalPrice = 0;
    let totalQuantity = 0;
    jQuery(".tx_product").each(function () {
      const quantity = parseInt(jQuery(this).find(".tx_quantity").text());
      const price = parseFloat(
        jQuery(this).find(".tx_price").text().replace("$", "")
      );
      if (quantity > 0) {
        const itemTotal = quantity * price;
        totalPrice += itemTotal;
        totalQuantity += quantity;
      }
    });
    jQuery("#tx_total-price").text("$" + totalPrice);
    if (totalQuantity > 0) {
      jQuery("#gform_submit_button_274").show();
    } else {
      jQuery("#gform_submit_button_274").hide();
    }
  }

  var productsArray = [];
  function summary_array() {
    productsArray.length = 0;
    jQuery(".tx_product").each(function () {
      var productId = jQuery(this).find(".tx_product_name").data("id");
      var quantity = jQuery(this).find(".tx_quantity").text();

      if (quantity > 0) {
        productsArray.push(productId + " [" + quantity + "]");
      }
      jQuery("#input_274_3").val(productsArray);
    });
  }
  jQuery(".quant").click(function () {
    summary_array();
    updateSummary();
  });

  var summaryArrayString = jQuery("#input_274_3").val();
  if (summaryArrayString) {
    var summaryArray = JSON.parse(summaryArrayString);
    summaryArray.forEach(function (item) {
      var productElement = jQuery(
        '.tx_product_name[data-id="' + item.id + '"]'
      ).parent('td').next('.tx_quantity-control').find(".tx_product");
      if (productElement.length) {
        productElement.find(".tx_quantity-control").find(".tx_quantity").text(item.quantity);
      }
      updateSummary();
    });
  }
	
 $('#gform_submit_button_274').on('submit', function() {
        // Add the loading class to the submit button
        $(this).find('input[type="submit"]').addClass('gf_submit_button_loading').find('::after').css('width', '100%');
    });
});
