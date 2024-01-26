// add to functions PHP
if ( class_exists( 'GFForms' ) ) {
	/**
	 * gravity_product_table
	 *
	 * @param	array<mixed>	$atts   	
	 * @param	string|null	$content	Default: null
	 * @return	string
	 */
  function gravity_product_table($atts = array()){
      $details = shortcode_atts( array(
          'event_id'=> get_post()->ID,
      ), $atts );
  
      $event_id = (empty($_GET['event_id']) ? $details['event_id']: $_GET['event_id']); 
      $form_id = 211;//id of form that creates ticket types
      $search_criteria = array(
          'status'        => 'active',
          'field_filters' => array(
              'mode' => 'all',
              array(
                  'key' => '34', //field id that contains an event id
                  'operator' => 'is',
                  'value' => $event_id
              ),array(
                  'key' => '49', //field that contains sale start date and time
                  'operator' => '<=',
                  'value' => date('Y-m-d H:i:s', strtotime('today'))
              ),array(
                  'key' => '50', //field that contains sale start date and time
                  'operator' => '>=',
                  'value' => date('Y-m-d H:i:s', strtotime('today'))
              )
          )
      );
      $paging = array( 'offset' => 0, 'page_size' => GFAPI::count_entries( $form_id, $search_criteria ) );
      $total_count = 0;
      $entries = GFAPI::get_entries( $form_id, $search_criteria,array('key' => '38', 'direction' => 'ASC'), $paging, $total_count ); //38 is the field id that contains the order
    
      $prod_table.='
      <div class="tx_container">
          <h3>'.get_the_title().'</h3>
          <table class="table">
              <tbody>';
              foreach($entries as $entry){
                  $get_thumbnail=(!empty($entry['41'])) ? $entry['41'] : $entry['55'];//41 is the field id that contains the file upload and 55 contains thumbnail link
                  $thumbnail = (!empty($get_thumbnail)) ? $get_thumbnail : get_the_post_thumbnail_url($event_id); 
                  $product_id = $event_id; // Your product ID
                  $description = $entry['30']; // The description you're checking
                  $total_sales = get_total_sales_for_description( $product_id, $description );
                  $inventory_limit = (!empty($entry['40'])) ? $entry['40'] : 100;//40 is the field id that contains the inventory limit
                  $inventory_left = $inventory_limit - $total_sales;
                  if($inventory_left  > 0){
                      $prod_table.='
                      <tr class="tx_product">
                          <td><img alt="'.$entry['30'].'" data-src="'.$thumbnail.'" class=" ls-is-cached lazyloaded" src="'.$thumbnail.'"></td>
                          <td class="tx_product_name" data-id="'.$entry['id'].'">'.$entry['30'].'</td>
                          <td class="tx_price"><span>'.$entry['11'].'</span></td>
                          <td class="tx_quantity-control">
                              <button type="button" class="tx_decrement quant">-</button>
                              <div class="tx_quantity">0</div>
                              <button type="button" data-inventory="'.$inventory_left.'" class="tx_increment quant">+</button>
                          </td>
                      </tr>';
                      }
                  }
                  $prod_table.='
                  </tbody>
                  <tfoot>
                      <td></td>
                      <td>
                          <p><b>Total</b>:
                      </td>
                      <td><span id="tx_total-price">$0</span></td>
                      <td></td>
                  </tfoot>
              </table>
          <div class="text-center">
    </div>
  </div><!--  end container -->';
      return $prod_table;
  }
}
add_shortcode( 'gravity_prod_table', 'gravity_product_table' );
