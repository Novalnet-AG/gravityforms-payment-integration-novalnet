<?php
/**
 * Novalnet payment method module
 * This module is used for real time processing of
 * Novalnet transaction of customers.
 *
 * Copyright ( c ) Novalnet AG
 *
 * Released under the GNU General Public License
 * This free contribution made by request.
 * If you have found this script useful a small
 * recommendation as well as a comment on merchant form
 * would be greatly appreciated.
 *
 * Script : class-novalnet-interface.php
 *
 */

 class Novalnet_interface {

    /**
     * Initialize Novalnet callback
     * $param $request
     *
     * @return boolean
     **/
    static function novalnet_callback_request ( $request ) {
        if ( isset( $request[ 'action_api' ] ) && 'novalnet_callback' == $request[ 'action_api' ] ) {
                do_action( 'gravity_forms_novalnet_callback' , $request );
        }
        return true;
    }

    /**
     * Novalnet admin template
     *
     * @return NULL
     **/
    static function novalnet_admin() {
        $post = $_POST;
        if ( rgar( $post, 'gf_novalnet_submit' ) ) {
            check_admin_referer( 'update', 'gf_novalnet_update' );
            $post = array_map( 'trim', $post );
            $post_values = $post;
            if ( !self::validate_backend( $post ) ) {
                $post_values[ 'vendor_id' ]  = ctype_digit( $post_values[ 'vendor_id' ] ) ? rgar( $post_values,'vendor_id' ) : '';
                $post_values[ 'auth_code' ]  = !empty( $post_values[ 'auth_code' ] ) ? rgar( $post_values,'auth_code' ) : '';
                $post_values[ 'product_id' ] = ctype_digit( $post_values[ 'product_id' ] ) ? rgar( $post_values,'product_id' ) : '';
                $post_values[ 'tariff_id' ]  = ctype_digit( $post_values[ 'tariff_id' ] ) ? rgar( $post_values,'tariff_id' ) : '';
                $post_values[ 'novalnet_error' ] = true;
                unset($post_values[ 'novalnet_message' ]);
            } else {
                $post_values[ 'novalnet_message' ] = true;
                unset($post_values[ 'novalnet_error' ]);
            }
            update_option( 'gf_novalnet_settings', $post_values );
        } else {
            $post_values = get_option( 'gf_novalnet_settings' );
        }
    ?>
        <form method='post' action=''>
            <img alt="<?php _e( "Novalnet AG", "gravityforms_novalnet" ) ?>" src="<?php echo NOVALNET_PLUGIN_BASE_URL; ?>/images/Novalnet.png" style="float:left;"/><br />
            <?php wp_nonce_field( "update", "gf_novalnet_update" ) ?><br />

            <?php
             !empty( $post[ 'novalnet_error' ] )&& !empty( $post_values[ 'novalnet_error' ] )  ? GFCommon::display_admin_message( array( rgar( $post, 'novalnet_error' ) ), '' ) : '';
            !empty( $post[ 'novalnet_message' ] ) && !empty( $post_values[ 'novalnet_message' ] ) ? GFCommon::display_admin_message( '', array( rgar( $post, 'novalnet_message' ) ) ) : '';
            ?>

            <table class="form-table">
                <tr>
                    <th scope="row" nowrap="nowrap"><label for="enable_module"><?php _e( "Enable payment method", "gravityforms_novalnet" ); ?></label> </th>
                    <td width="88%">
                        <input type="radio" name="enable_module" id="enable_module" value="1" <?php echo ( "1" == rgar( $post_values, 'enable_module' ) ) ? "checked='checked'" : "" ?>/>
                        <label class="inline" for="enable_module"><?php _e( "Yes", "gravityforms_novalnet" ); ?></label>
                        &nbsp;&nbsp;&nbsp;
                        <input type="radio" name="enable_module" id="enable_module" value="0" <?php echo ( "0" == rgar( $post_values, 'enable_module' ) || "" == rgar( $post_values, 'enable_module' ) ) ? "checked='checked'" : "" ?>/>
                        <label class="inline" for="enable_module"><?php _e( "No", "gravityforms_novalnet" ); ?></label>
                        <br/>
                        <i></i>
                    </td>
                </tr>
                <tr>
                    <th scope="row" nowrap="nowrap"><label for="test_mode"><?php _e( "Enable test mode", "gravityforms_novalnet" ); ?></label> </th>
                    <td width="88%">
                        <input type="radio" name="test_mode" id="test_mode" value="1" <?php echo  ( "1" == rgar( $post_values, 'test_mode' ) ) ? "checked='checked'" : "" ?>/>
                        <label class="inline" for="test_mode"><?php _e( "Yes", "gravityforms_novalnet" ); ?></label>
                        &nbsp;&nbsp;&nbsp;
                        <input type="radio" name="test_mode" id="test_mode" value="0" <?php echo ( "0" == rgar( $post_values, 'test_mode' ) || "" == rgar( $post_values, 'test_mode' ) ) ? "checked='checked'" : "" ?>/>
                        <label class="inline" for="test_mode"><?php _e( "No", "gravityforms_novalnet" ); ?></label>
                        <br/>
                        <i><?php _e( "The payment will be processed in the test mode therefore amount for this transaction will not be charged", "gravityforms_novalnet" ); ?></i>
                    </td>
                </tr>
                <tr>
                    <th scope="row" nowrap="nowrap"><label for="vendor_id"><?php _e( "Merchant ID", "gravityforms_novalnet" ); ?></label> </th>
                    <td width="88%">
                        <input class="size-1" id="vendor_id" name="vendor_id" value="<?php echo esc_attr( rgar( $post_values,"vendor_id" ) ) ?>" />
                        <br/>
                        <i><?php _e( "Enter Novalnet merchant ID", "gravityforms_novalnet" ); ?></i>
                    </td>
                </tr>
                <tr>
                    <th scope="row" nowrap="nowrap"><label for="auth_code"><?php _e( "Authentication code", "gravityforms_novalnet" ); ?></label> </th>
                    <td width="88%">
                        <input class="size-1" id="auth_code" name="auth_code" value="<?php echo esc_attr( rgar( $post_values,"auth_code" ) ) ?>" />
                        <br/>
                        <i><?php _e( "Enter Novalnet authentication code", "gravityforms_novalnet" ); ?></i>
                    </td>
                </tr>
                <tr>
                    <th scope="row" nowrap="nowrap"><label for="product_id"><?php _e( "Project ID", "gravityforms_novalnet" ); ?></label> </th>
                    <td width="88%">
                        <input class="size-1" id="product_id" name="product_id" value="<?php echo esc_attr( rgar( $post_values,"product_id" ) ) ?>" />
                        <br/>
                        <i><?php _e( "Enter Novalnet project ID", "gravityforms_novalnet" ); ?></i>
                    </td>
                </tr>
                <tr>
                    <th scope="row" nowrap="nowrap"><label for="tariff_id"><?php _e( "Tariff ID", "gravityforms_novalnet" ); ?></label> </th>
                    <td width="88%">
                        <input class="size-1" id="tariff_id" name="tariff_id" value="<?php echo esc_attr( rgar( $post_values,"tariff_id" ) ) ?>" />
                        <br/>
                        <i><?php _e( "Enter Novalnet tariff ID", "gravityforms_novalnet" ); ?></i>
                    </td>
                </tr>
                <tr>
                    <th scope="row" nowrap="nowrap"><label for="payment_due_date"><?php _e( "Payment due date (in days)", "gravityforms_novalnet" ); ?></label> </th>
                    <td width="88%">
                        <input class="size-1" id="payment_due_date" name="payment_due_date" value="<?php echo esc_attr( rgar( $post_values,"payment_due_date" ) ) ?>" />
                        <br/>
                        <i><?php _e( "Enter the number of days to transfer the payment amount to Novalnet (must be greater than 7 days). In case if the field is empty, 14 days will be set as due date by default", "gravityforms_novalnet" ); ?></i>
                    </td>
                </tr>
                <tr>
                    <th scope="row" nowrap="nowrap"><label for="order_completion_status"><?php _e( "Order completion status", 'gravityforms_novalnet' ); ?></label> </th>
                    <td width="88%">
                        <select class="size-1" id="order_completion_status" name='order_completion_status'>
                            <option value='<?php echo _e( 'Pending', 'gravityforms_novalnet' ); ?>' <?php if ( 'Pending' == esc_attr( rgar( $post_values,"order_completion_status" ) ) || 'Offen' == esc_attr( rgar( $post_values,"order_completion_status" ) ) ){ echo 'selected="selected"'; } ?> ><?php _e( 'Pending', 'gravityforms_novalnet' ); ?></option>
                            <option value='<?php echo _e( 'Paid', 'gravityforms_novalnet' ); ?>' <?php if ( 'Paid' == esc_attr( rgar( $post_values,'order_completion_status' ) ) || 'Bezahlt' == esc_attr( rgar( $post_values,"order_completion_status" ) ) ){ echo 'selected="selected"'; } ?> ><?php _e( 'Paid', 'gravityforms_novalnet' ); ?></option>
                            <option value='<?php echo _e( 'Failed', 'gravityforms_novalnet' ); ?>' <?php if ( 'Failed' == esc_attr( rgar( $post_values,'order_completion_status' ) ) || 'Fehlgeschlagen' == esc_attr( rgar( $post_values,"order_completion_status" ) ) ){ echo 'selected="selected"'; } ?> ><?php _e( 'Failed', 'gravityforms_novalnet' ); ?></option>
                            <option value='<?php echo _e( 'Processing', 'gravityforms_novalnet' ); ?>' <?php if ( 'Processing' == esc_attr( rgar( $post_values,"order_completion_status" ) ) || 'Bearbeitung' == esc_attr( rgar( $post_values,"order_completion_status" ) ) ){ echo 'selected="selected"'; } ?> ><?php _e( 'Processing', 'gravityforms_novalnet' ); ?></option>
                        </select>
                        <br/>
                        <i></i>
                    </td>
                </tr>
                <tr>
                    <th scope="row" nowrap="nowrap"><label for="callback_order_status"><?php _e( "Callback order status", "gravityforms_novalnet" ); ?></label> </th>
                    <td width="88%">
                        <select class="size-1" id="callback_order_status" name='callback_order_status'>
                            <option value='<?php echo _e( 'Pending', 'gravityforms_novalnet' ); ?>' <?php if ( 'Pending' == esc_attr( rgar( $post_values,"callback_order_status" ) ) || 'Offen' == esc_attr( rgar( $post_values,"callback_order_status" ) ) ){ echo 'selected="selected"'; } ?> ><?php _e( 'Pending', 'gravityforms_novalnet' ); ?></option>
                            <option value='<?php echo _e( 'Paid', 'gravityforms_novalnet' ); ?>' <?php if ( 'Paid' == esc_attr( rgar( $post_values,'callback_order_status' ) ) || 'Bezahlt' == esc_attr( rgar( $post_values,"callback_order_status" ) ) ){ echo 'selected="selected"'; } ?> ><?php _e( 'Paid', 'gravityforms_novalnet' ); ?></option>
                            <option value='<?php echo _e( 'Failed', 'gravityforms_novalnet' ); ?>' <?php if ( 'Failed' == esc_attr( rgar( $post_values,'callback_order_status' ) ) || 'Fehlgeschlagen' == esc_attr( rgar( $post_values,"callback_order_status" ) ) ){ echo 'selected="selected"'; } ?> ><?php _e( 'Failed', 'gravityforms_novalnet' ); ?></option>
                            <option value='<?php echo _e( 'Processing', 'gravityforms_novalnet' ); ?>' <?php if ( 'Processing' == esc_attr( rgar( $post_values,"callback_order_status" ) ) || 'Bearbeitung' == esc_attr( rgar( $post_values,"callback_order_status" ) ) ){ echo 'selected="selected"'; } ?> ><?php _e( 'Processing', 'gravityforms_novalnet' ); ?></option>
                        </select>
                        <br/>
                        <i></i>
                    </td>
                </tr>
                <tr>
                    <th scope="row" nowrap="nowrap"><label for="failed_order_status"><?php _e( "Cancel status", "gravityforms_novalnet" ); ?></label> </th>
                    <td width="88%">
                        <select class="size-1" id="failed_order_status" name='failed_order_status'>
                            <option value='<?php echo _e( 'Pending', 'gravityforms_novalnet' ); ?>' <?php if ( 'Pending' == esc_attr( rgar( $post_values,"failed_order_status" ) )  || 'Offen' == esc_attr( rgar( $post_values,"failed_order_status" ) ) ){ echo 'selected="selected"'; } ?> ><?php _e( 'Pending', 'gravityforms_novalnet' ); ?></option>
                            <option value='<?php echo _e( 'Paid', 'gravityforms_novalnet' ); ?>' <?php if ( 'Paid' == esc_attr( rgar( $post_values,'failed_order_status' ) ) || 'Bezahlt' == esc_attr( rgar( $post_values,"failed_order_status" ) ) ){ echo 'selected="selected"'; } ?> ><?php _e( 'Paid', 'gravityforms_novalnet' ); ?></option>
                            <option value='<?php echo _e( 'Failed', 'gravityforms_novalnet' ); ?>' <?php if ( 'Failed' == esc_attr( rgar( $post_values,'failed_order_status' ) ) || 'Fehlgeschlagen' == esc_attr( rgar( $post_values,"failed_order_status" ) ) ){ echo 'selected="selected"'; } ?> ><?php _e( 'Failed', 'gravityforms_novalnet' ); ?></option>
                            <option value='<?php echo _e( 'Processing', 'gravityforms_novalnet' ); ?>' <?php if ( 'Processing' == esc_attr( rgar( $post_values,"failed_order_status" ) ) || 'Bearbeitung' == esc_attr( rgar( $post_values,"failed_order_status" ) ) ){ echo 'selected="selected"'; } ?> ><?php _e( 'Processing', 'gravityforms_novalnet' ); ?></option>
                        </select>
                        <br/>
                        <i></i>
                    </td>
                </tr>
                <tr>
                    <th scope="row" nowrap="nowrap"><label for="referrer_id"><?php _e( "Referrer ID", "gravityforms_novalnet" ); ?></label> </th>
                    <td width="88%">
                        <input class="size-1" id="referrer_id" name="referrer_id" value="<?php echo esc_attr( rgar( $post_values,"referrer_id" ) ) ?>" />
                        <br/>
                        <i><?php _e( "Enter the referrer ID of the person/company who recommended you Novalnet", "gravityforms_novalnet" ); ?></i>
                    </td>
                </tr>
                <tr>
                    <th scope="row" nowrap="nowrap"><label for="reference_1"><?php _e( "Transaction reference 1", "gravityforms_novalnet" ); ?></label> </th>
                    <td width="88%">
                        <input class="size-1" id="reference_1" name="reference_1" value="<?php echo esc_attr( rgar( $post_values,"reference_1" ) ) ?>" />
                        <br/>
                        <i><?php _e( "This reference will appear in your bank account statement", "gravityforms_novalnet" ); ?></i>
                    </td>
                </tr>
                <tr>
                    <th scope="row" nowrap="nowrap"><label for="reference_2"><?php _e( "Transaction reference 2", "gravityforms_novalnet" ); ?></label> </th>
                    <td width="88%">
                        <input class="size-1" id="reference_2" name="reference_2" value="<?php echo esc_attr( rgar( $post_values,"reference_2" ) ) ?>" />
                        <br/>
                        <i><?php _e( 'This reference will appear in your bank account statement', 'gravityforms_novalnet' ); ?></i>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" ><input type="submit" name="gf_novalnet_submit" class="button-primary" value="<?php _e( "Save Settings", "gravityforms_novalnet" ) ?>" /></td>
                </tr>
            </table>

            <input type= 'hidden' name = 'novalnet_message' value= '<?php _e( "Updated", "gravityforms_novalnet" ) ?>'/>
            <input type= 'hidden' name = 'novalnet_error' value= '<?php _e( 'Please fill in all the mandatory fields', 'gravityforms_novalnet') ?>'/>
        </form>
        <?php
    }

    /**
     * Backend validation
     * $param $values
     *
     * @return boolean
     **/
    static function validate_backend( $values ) {
        $values = array_map( 'trim', $values );
        return ( ctype_digit( $values[ 'vendor_id' ] ) &&  !empty( $values[ 'auth_code' ] ) && ctype_digit( $values[ 'product_id' ] ) && ctype_digit( $values[ 'tariff_id' ] ) ) ? true : false;
    }

    /**
     * Check for Novalnet page
     *
     * @return boolean
     **/
    static function is_novalnet_page(){
        $current_page = trim( strtolower( RGForms::get( "subview" ) ) );
        return in_array( $current_page, array( "novalnet" ) );
    }

    /**
     * Return URL of shop
     * @params $form_id
     * @params $lead_id
     *
     * @return string
     **/
    static function novalnet_return_url( $form_id, $lead_id ) {
        $ids_query = "ids={$form_id}|{$lead_id}";
        $ids_query .= "&hash=" . wp_hash( $ids_query );
        return add_query_arg( 'gf_novalnet_return', base64_encode( $ids_query ), home_url( '/' ) );
    }

    /**
     * Preparing Novalnet comments
     * @params $response
     * @params $test_mode
     * @params $product_id
     * @params $order_status
     *
     * @return string
     **/
    static function novalnet_comments( $response, $test_mode, $product_id, $order_status ) {
        $line_break = PHP_EOL;
        $comments  = $line_break . __( 'Order status : ','gravityforms_novalnet' ) . $order_status;
        $comments .= $line_break . __( 'Novalnet transaction ID : ', 'gravityforms_novalnet' )  . rgar( $response, 'tid' );
        $comments .= ( rgar( $response, 'test_mode' ) || $test_mode ) ? $line_break . __( 'Test order','gravityforms_novalnet' ) : '';
        if ( 100 != rgar( $response, 'status' ) ) {
            $comments .=  ( !empty( $response[ 'status_text' ] ) ) ? $line_break . $response[ 'status_text' ] : ( ( !empty( $response[ 'status_desc' ] ) ) ? $line_break . $response[ 'status_desc' ] : '' );
        }else{
            $comments .= $line_break.__( 'Payment method : ','gravityforms_novalnet' ).rgar( $response,  'payment_name' );
            if ( 27 == rgar( $response, 'key' ) ) {
                $comments .=$line_break . $line_break . __( 'Please transfer the amount to the below mentioned account details of our payment processor Novalnet','gravityforms_novalnet' );
                $comments .= $line_break . __( 'Account holder :','gravityforms_novalnet' ) . ' ' . __( 'NOVALNET AG','gravityforms_novalnet' ) ;
                $comments .= ( rgar( $response, 'due_date' ) != '' ) ? $line_break.__( 'Due date : ','gravityforms_novalnet' ) . ' ' . rgar( $response, 'due_date' ) : $line_break;
                $comments .= $line_break . __( 'IBAN : ','gravityforms_novalnet' ) . rgar( $response, 'invoice_iban' );
                $comments .= $line_break . __( 'BIC : ','gravityforms_novalnet' ) . rgar( $response, 'invoice_bic' );
                $comments .= $line_break . __( 'Bank : ','gravityforms_novalnet' ) . rgar( $response, 'invoice_bankname' ) . ' ' . rgar( $response, 'invoice_bankplace' );
                $comments .= $line_break . __( 'Amount : ','gravityforms_novalnet' ) . ' ' . GFCommon::to_money( rgar( $response, 'amount' ), rgar( $response, 'currency' ) );
                $comments .= $line_break . __( 'Reference 1 : TID ','gravityforms_novalnet' ) .  rgar( $response, 'tid' );
                $comments .= $line_break . __( 'Reference 2 : BNR-','gravityforms_novalnet' ) . $product_id. '-' . rgar( $response, 'order_no' );
                $comments .= $line_break . __( 'Reference 3 : Order number ','gravityforms_novalnet' ) . rgar( $response, 'order_no' );
            }
        }
        return $comments;
    }

    /**
     * Sends the request for the payments
     * @param $params
     * @param $form_url
     *
     * @return string
     **/
    static function send_form( $params, $form_url ) {
        if ( is_array( $params ) ) {
            $frmData = '';
            $frmData.= __( 'Thank you for choosing Novalnet payment','gravityforms_novalnet' );
            $frmData.= '<form name="frmnovalnet" method="post" action="' . $form_url . '">';
            foreach ( $params as $k => $v )
                $frmData .= '<input type="hidden" name="' . $k . '" value="' . $v . '" />' . "\n";
            $frmData .= '<input type="submit" id="enter" name="enter" value="'.__( 'Pay via Novalnet','gravityforms_novalnet' ).'" /></form>';
            $frmData .= '<script type="text/javascript" language="javascript">window.onload = function() { document.getElementById( "enter" ).disabled="true";document.forms.frmnovalnet.submit(); } </script>';
            return  $frmData;
        }
    }

    /**
     * Function to return the client IP Address
     *
     * @return string
     **/
    static function get_client_IP () {
        $ipaddress = '';
        if ( isset( $_SERVER[ 'HTTP_CLIENT_IP' ] ) )
          $ipaddress = $_SERVER[ 'HTTP_CLIENT_IP' ];
        else if ( isset( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) )
          $ipaddress = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
        else if ( isset( $_SERVER[ 'HTTP_X_FORWARDED' ] ) )
          $ipaddress = $_SERVER[ 'HTTP_X_FORWARDED' ];
        else if ( isset( $_SERVER[ 'HTTP_FORWARDED_FOR' ] ) )
          $ipaddress = $_SERVER[ 'HTTP_FORWARDED_FOR' ];
        else if ( isset( $_SERVER[ 'HTTP_FORWARDED' ] ) )
          $ipaddress = $_SERVER[ 'HTTP_FORWARDED' ];
        else if ( isset( $_SERVER[ 'REMOTE_ADDR' ] ) )
          $ipaddress = $_SERVER[ 'REMOTE_ADDR' ];
        else
          $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    /**
     * Check for Novalnet access
     * @param $required_permission
     *
     * @return NULL
     **/
    static function has_novalnet_access( $required_permission ){
        $has_members_plugin = function_exists( 'members_get_capabilities' );
        $has_access = $has_members_plugin ? current_user_can( $required_permission ) : current_user_can( "level_7" );
        if ( $has_access ){
            return $has_members_plugin ? $required_permission : "level_7";
        }else {
            return false;
        }
    }

    /**
     * Novalnet plugin uninstall
     *
     * @return Null
     **/
    static function novalnet_plugin_uninstall() {
        delete_option( 'gf_novalnet_settings' );
    }

    /**
     * Form basic params
     * @params $form
     * @params $entry
     *
     * @return array
     **/
    static function novalnet_basic_param( $form, $entry ) {
        $amount = self::get_donation_discount_query_string( $form, $entry );
        $data = array();
        include_once( GFCommon::get_base_path(). '/includes/fields/class-gf-field-address.php' );
        $get_country = new GF_Field_Address();
        foreach ( $form[ 'fields' ] as $field ) {
            $id = $field[ 'id' ];
            switch( GFFormsModel::get_input_type( $field ) ){
                case 'name':
                    $data[ 'first_name' ] = rgpost( "input_{$id}_3" );
                    $data[ 'last_name' ] = rgpost( "input_{$id}_6" );
                    break;

                case 'email':
                    $data[ 'email' ] = rgpost( "input_{$id}" );

                    break;

                case 'phone':
                    $data[ 'phone' ] = rgpost( "input_{$id}" );
                    break;

                case 'address':
                    $data[ 'zip' ] = trim( rgpost( "input_{$id}_5" ) );
                    $parts = array( trim( rgpost( "input_{$id}_1" ) ), trim( rgpost( "input_{$id}_2" ) ) );
                    $data[ 'street' ] = implode( ', ', array_filter( $parts, 'strlen' ) );
                    $data[ 'city' ] = trim( rgpost( "input_{$id}_3" ) );
                    $data[ 'state' ] = trim( rgpost( "input_{$id}_4" ) );
                    $data[ 'country' ] = $data[ 'country_code' ] = $get_country->get_country_code( rgpost( "input_{$id}_6" ) );
                    break;

                case 'total':
                    $amount = GFCommon::to_number( rgpost( "input_{$id}" ) );
                    break;
            }
        }

        $data[ 'currency' ]     = GFCommon::get_currency();
        $data[ 'amount' ]     = $amount*100;
        $data[ 'search_in_street' ] = 1;
        $data[ 'gender' ] = 'm';
        $data[ 'use_utf8' ] = 1;
        $data[ 'lang' ] = $data[ 'language' ] = strtoupper( substr( get_bloginfo( 'language' ), 0, 2 ) );
        $data[ 'return_url' ] = $data[ 'error_return_url' ] = self::novalnet_return_url( $form["id"], $entry["id"] );
        $data[ 'error_return_method' ] = $data[ 'return_method' ] = 'POST';
        $data[ 'system_name' ]    = 'Wordpress-GravityForm';
        $data[ 'system_version' ] = 'Wordpress-GravityForm-'.get_option( 'rg_form_version' ).'-NN1.0.0';
        $data[ 'system_url' ] = site_url();
        return array_filter( $data );
    }

    /**
     * Get valid amount
     * @params $form
     * @params $entry
     *
     * @return Integer
     **/
    static function get_donation_discount_query_string( $form, $entry ){
         $fields = "";
         $donations = GFCommon::get_fields_by_type( $form, array( 'donation' ) );
         $products = GFCommon::get_product_fields( $form, $entry, true );
         $total = 0;
         $product_index = 1;
         $discount = 0;
         foreach( $donations as $donation ) {
             $value = RGFormsModel::get_lead_field_value( $entry, $donation );
             list( $name, $price ) = explode( '|', $value );
             if ( empty( $price ) ){
                 $price = $name;
                 $name = $donation["label"];
             }
             $purpose .= $name . ", ";
             $price = GFCommon::to_number( $price );
             $total = $price;
         }

         if ( 0 == $total ){
             $total = GFCommon::get_order_total( $form, $entry );
         }
         foreach( $products["products"] as $product ){
             $option_fields = "";
             $price = GFCommon::to_number( $product["price"] );
             if ( !empty( $product[ 'options' ] ) && is_array( $product[ 'options' ] ) ){
                 $option_index = 1;
                 foreach( $product["options"] as $option ){
                     $field_label = urlencode( $option[ 'field_label' ] );
                     $option_name = urlencode( $option[ 'option_name' ] );
                     $option_fields .= "&on{$option_index}_{$product_index}={$field_label}&os{$option_index}_{$product_index}={$option_name}";
                     $price += GFCommon::to_number( $option[ 'price' ] );
                     $option_index++;
                     if ( $price > 0 ) {
                         $total += $price * $product[ 'quantity' ];
                         $product_index++;
                     }else{
                         $discount += abs( $price ) * $product[ 'quantity' ];
                     }
                 }
             }
         }
         if ( $discount > 0 ){
             $total = $total - $discount;
         }
         return $total;
     }

    /**
     * Form Novalnet params
     * @params $confirmation
     * @params $form
     * @params $entry
     *
     * @return Integer
     **/
    static function prepare_novalnet_params( $confirmation, $form, $entry ) {
        global $wpdb;
        if ( RGForms::post( "gform_submit" ) != $form["id"] ) {
            $post = $_POST;
            $additional_note = self::novalnet_server_response( $form, $entry, $post );
            return ( isset( $post[ 'status' ] ) && 100 == rgar( $post,'status' ) || ( 90 == rgar( $post,'status' ) && 'paypal' == rgar( $post,'payment_type' ) ) ) ? __( 'Order number : ', 'gravityforms_novalnet') . $post[ 'order_no' ] . PHP_EOL . $confirmation . $additional_note : __( 'Unfortunately, this order could not be processed. Please, place a new order ', 'gravityforms_novalnet' ) . $additional_note;
        }
        $backend_values = get_option( "gf_novalnet_settings" );
        $backend_values = array_map( 'trim', $backend_values );
        $order_id = apply_filters( "gform_novalnet_invoice", "", $form, $entry );
        $client_ip = self::get_client_IP();
        $params = self::novalnet_basic_param( $form, $entry );
        $params[ 'order_no' ]       = ( empty( $order_id ) ) ? $entry[ 'id' ] : $order_id;
        $params[ 'vendor' ]         = rgar( $backend_values,'vendor_id' );
        $params[ 'auth_code' ]      = rgar( $backend_values,'auth_code' );
        $params[ 'product' ]        = rgar( $backend_values,'product_id' );
        $params[ 'tariff' ]         = rgar( $backend_values,'tariff_id' );
        if( ( 0 != $backend_values[ 'payment_due_date' ] ) && ctype_digit( $backend_values[ 'payment_due_date' ] ) ) {
			$params[ 'due_date' ]       = rgar( $backend_values,'payment_due_date' );
		}
        $params[ 'test_mode' ]      = !empty($backend_values[ 'test_mode' ]) ? rgar( $backend_values,'test_mode' ) : 0;
        $params[ 'system_ip' ]      = ( '::1' == $_SERVER[ 'SERVER_ADDR' ] ) ? '127.0.0.1' : $_SERVER[ 'SERVER_ADDR' ];
        $params[ 'remote_ip' ]      = ( '::1' == $client_ip ) ? '127.0.0.1' : $client_ip;
        if ( !empty( $backend_values[ 'referrer_id' ] ) )
            $params[ 'referrer_id' ]      = rgar( $backend_values,'referrer_id' );
        $arrayValues = array( '1'=>'reference_1','2' => 'reference_2' );
        foreach( $arrayValues as $k =>$v ) {
            $reference   = trim( strip_tags( rgar( $backend_values, $v ) ) );
            if ( !empty( $reference ) ) {
                $params[ 'input' . $k]    = 'reference' . $k;
                $params[ 'inputval' . $k] = $reference;
            }
        }
        $wpdb->update( GFFormsModel::get_lead_table_name(), array( 'payment_amount' => ( $params[ 'amount' ]/100 ) ), array( 'id' => $params[ 'order_no' ] ) );
        return self::send_form( $params, 'https://payport.novalnet.de/nn/paygate.jsp' );
    }

    /**
     * Novalnet thankyou page
     *
     * @return string
     **/
    static function novalnet_thankyou_page () {
        if ( $str = RGForms::get( "gf_novalnet_return" ) ){
            $str = base64_decode( $str );
            parse_str( $str, $query );
            if ( $query["hash"] == wp_hash( "ids=" . $query["ids"] ) ){
                list( $form_id, $lead_id ) = explode( "|", $query["ids"] );
                $form = RGFormsModel::get_form_meta( $form_id );
                $lead = RGFormsModel::get_lead( $lead_id );
                if ( !class_exists( "GFFormDisplay" ) )
                    require_once( GFCommon::get_base_path() . "/form_display.php" );
                $confirmation = GFFormDisplay::handle_confirmation( $form, $lead, false );
                if ( is_array( $confirmation ) && isset( $confirmation["redirect"] ) ){
                    header( "Location: {$confirmation["redirect"]}" );
                    exit;
                }
                GFFormDisplay::$submission[ $form_id ] = array( "is_confirmation" => true, "confirmation_message" => nl2br( $confirmation ), "form" => $form, "lead" => $lead );
            }
        }
    }

    /**
     * Process Novalnet server response
     * @params $form
     * @params $entry
     *
     * @return NULL
     **/
    static function novalnet_server_response( $form, $entry, $novalnet_response ) {
        global $wpdb;
        $table_name = RGFormsModel::get_lead_table_name();
        $order_id = empty( $novalnet_response[ 'order_no' ] ) ? $entry[ 'id' ] :  rgar( $novalnet_response, 'order_no' );

        $check = $wpdb->get_var( $wpdb->prepare( "SELECT transaction_id FROM {$table_name} WHERE id = %s", $order_id ) );
        $novalnet_response[ 'payment_name' ] = self::get_novalnet_payment_details( rgar( $novalnet_response, 'key' ), rgar( $novalnet_response, 'payment_type' ) );
        if ( empty( $check ) ) {
            $backend_values = get_option( 'gf_novalnet_settings' );
            $order_status = ( 100 != rgar( $novalnet_response, 'status' ) ) ? rgar( $backend_values,'failed_order_status' ) : rgar( $backend_values, 'order_completion_status' );
            $novalnet_order_comments = self::novalnet_comments( $novalnet_response, rgar( $backend_values,'test_mode' ), rgar( $backend_values,'product_id' ), $order_status );
            $backend_values[ 'payment_type' ] = rgar( $novalnet_response,'payment_type' );
            self::novalnet_db_update( $order_id, $order_status, $novalnet_order_comments, $novalnet_response, $entry );
            if ( 100 == rgar( $novalnet_response,'status' ) || ( 90 == rgar( $novalnet_response,'status' ) && 'paypal' == rgar( $novalnet_response,'payment_type' ) ) ) {
                 $lead = RGFormsModel::get_lead( $order_id );
                foreach( $form[ 'notifications' ] as $key => $value )
                    $notification = $value;
                $notification[ 'to' ]     = rgar( $novalnet_response, 'email' );
                $notification[ 'toType' ] = 'email';
                $notification[ 'attachments' ] = '';
                $notification[ 'subject' ] = get_option( 'blogname' ).' Order ID : ' . $order_id;
                $notification[ 'fromName' ] = 'Novalnet';
                $notification[ 'message' ] .= $novalnet_order_comments;
                GFCommon::send_notification( $notification, $form, $entry );
                $param[ 'order_id' ] = $order_id;
                $param[ 'callback_amount' ] = ( 27 == rgar( $novalnet_response, 'key' ) || ( 'paypal' == rgar( $novalnet_response, 'payment_type' ) && 90 == rgar( $novalnet_response, 'status' ) ) ) ? 0 : ( rgar( $novalnet_response, 'amount' )*100 );
                $param[ 'reference_tid' ] = rgar( $novalnet_response, 'tid' );
                $param[ 'callback_datetime' ] = date( 'Y-m-d H:i:s' );
                $param[ 'callback_tid' ] = '';
                $param[ 'callback_log' ] = site_url();
                $param[ 'total_amount' ] = rgar( $novalnet_response, 'amount' )*100;
                $param[ 'class_name' ] = 'novalnet_' . strtolower( ( empty( $novalnet_response['payment_type'] ) && 37 == rgar( $novalnet_response, 'key' ) ) ? 'sepa' : rgar( $novalnet_response, 'payment_type' ) );
                $wpdb->insert( $wpdb->prefix.'rg_novalnet_callback', $param );
                self::novalnet_postback( $backend_values, $novalnet_response, $order_id );
            }else{
               $trans_status_desc = ( !empty( $novalnet_response[ 'status_text' ] ) ) ? $novalnet_response[ 'status_text' ] : ( ( !empty( $novalnet_response[ 'status_desc' ] ) ) ? $novalnet_response[ 'status_desc' ] : '' );
                echo "<script>alert( '$trans_status_desc' );</script>";
            }
            return $novalnet_order_comments;
        }
        return '';
    }

    /**
     * Send postback params
     * @param $backend_values
     * @param $response
     *
     * @return boolean
     **/
    static function novalnet_postback( $backend_values, $response, $order_id ) {
        $call_back_params = array( 'vendor'    => rgar( $backend_values, 'vendor_id' ),
                                   'auth_code' => rgar( $backend_values, 'auth_code' ),
                                   'product' => rgar( $backend_values, 'product_id' ),
                                   'tariff' => rgar( $backend_values, 'tariff_id' ),
                                   'key' => rgar( $response, 'key' ),
                                   'status' => '100',
                                   'tid' => rgar( $response, 'tid' ),
                                   'order_no' => $order_id );
        if ( 27 == $call_back_params[ 'key' ] ) {
            $call_back_params[ 'invoice_ref' ] = 'BNR-' . $call_back_params[ 'product' ] . '-' . $call_back_params[ 'order_no' ];
            $call_back_params[ 'invoice_type' ] = ( 'invoice' == rgar( $backend_values, 'payment_type' ) ? 'INVOICE' : 'PREPAYMENT' );
        }
        if ( !array_search( '', $call_back_params ) ) {
            self::curl_https_request( 'payport.novalnet.de/paygate.jsp', $call_back_params );
            return true;
        }
        return false;
    }

    /**
     * Function to communicate transaction parameters with Novalnet paygate
     * @param $form_url
     * @param $url_params
     *
     * @return array
     **/
    static function curl_https_request ( $form_url, $url_params ) {
        $ssl = ( false === is_ssl() ) ? 'http://' : 'https://';
        $form_url = $ssl . $form_url;
        $ch=curl_init( $form_url );
        curl_setopt( $ch, CURLOPT_POST,1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $url_params );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 240 );
        curl_exec( $ch );
        curl_close( $ch );
    }

    /**
     * Return payment name
     * @param $payment_key
     * @param $payment_type
     *
     * @return array
     **/
    static function get_novalnet_payment_details( $payment_key = '', $payment_type = '' ) {
        if( !empty( $payment_key ) ) {
            $array_values = array(
                '6'  => __( 'Novalnet Credit card','gravityforms_novalnet' ),
                '37' => __( 'Novalnet Direct Debit SEPA','gravityforms_novalnet' ),
                '27' => ( 'invoice' == $payment_type ) ? __( 'Novalnet Invoice','gravityforms_novalnet' ) : __( 'Novalnet Prepayment','gravityforms_novalnet' ),
                '34' => __( 'Novalnet PayPal','gravityforms_novalnet' ),
                '49' => __( 'Novalnet iDEAL','gravityforms_novalnet' ),
                '50' => __( 'Novalnet EPS','gravityforms_novalnet' ),
                '33' => __( 'Novalnet Insatnt Bank Transfer','gravityforms_novalnet' )
            );
            return ( 'invoice_prepayment' == $payment_type ) ? __( 'Novalnet Invoice / Prepayment','gravityforms_novalnet' ) : $array_values[ $payment_key ];
        }
        return '';
    }

    /**
     * Add permission to Novalnet plugin
     * @param $caps
     *
     * @return NULL
     **/
    static function novalnet_capabilities( $caps ) {
        return array_merge( $caps, array( "gravityforms_novalnet", "gravityforms_novalnet_uninstall" ) );
    }

    /**
     * Update Novalnet details in DB
     * @param $order_id
     * @param $order_status
     * @param $novalnet_order_comments
     * @param $novalnet_response
     * @param $entry
     *
     * @return NULL
     **/
    static function novalnet_db_update( $order_id, $order_status, $novalnet_order_comments, $novalnet_response, $entry ) {
        global $wpdb;
        $novalnet_addon = new Novalnet_core;
        if ( empty( $entry ) ) {
            $failed_params = array( 'payment_status' => $order_status,
                                    'note' => $novalnet_order_comments  );
            $novalnet_addon->fail_payment( array( 'id' => $order_id ), $failed_params );
        }else{
            $completed_params = array( 'transaction_type' => rgar( $novalnet_response, 'payment_type' ),
                                       'payment_status' => $order_status,
                                       'amount' => rgar( $novalnet_response, 'amount' ),
                                       'payment_method' => 'novalnet_' . strtolower( ( empty( $novalnet_response['payment_type'] ) && 37 == rgar( $novalnet_response, 'key' ) ) ? 'sepa' : rgar( $novalnet_response, 'payment_type' ) ),
                                       'transaction_id' => rgar( $novalnet_response, 'tid' ),
                                       'payment_date' => date( 'Y-m-d H:i:s' ),
                                       'note' => $novalnet_order_comments );
            $novalnet_addon->complete_payment( $entry,  $completed_params );
        }

        gform_update_meta( $order_id, 'payment_gateway', 'novalnet_' . strtolower( ( empty( $novalnet_response['payment_type'] ) && 37 == rgar( $novalnet_response, 'key' ) ) ? 'sepa' : rgar( $novalnet_response, 'payment_type' ) ) );

        RGFormsModel::update_lead_property( $order_id, 'payment_status', $order_status );
    }

    /**
     * Process Novalnet callback
     * @param $request
     *
     * @return NULL
     **/
    static function novalnet_callback_process( $request ) {
        global $wpdb;
        include_once( NOVALNET_BASE_PATH.'/callback/callback_novalnet2gravityforms.php' );
        unset( $request[ 'action_api' ] );
        $ary_capture_params  = $request; // Assign callback parameters
        $nn_vendor_script = new Novalnet_vendor_script( $ary_capture_params ); // Novalnet Callback Class Object
        $nn_trans_history = $nn_vendor_script->get_order_reference(); // Order reference of given callback request
        $nn_capture_params = $nn_vendor_script->get_capture_params(); // Collect callback capture parameters
        $amount = $nn_capture_params[ 'amount' ];
        if ( !empty( $nn_trans_history ) ) {
            if ( isset( $nn_capture_params[ 'vendor_activation' ] ) && 1 == $nn_capture_params[ 'vendor_activation' ] ) {
                $nn_vendor_script->update_aff_account_activation_detail( $nn_capture_params );
            }else{
                $order_id = $nn_trans_history[ 'order_id' ]; // Given shop order ID
                $order_payment = $nn_trans_history[ 'payment_type' ]; // Executed payment type for original transaction
                if ( 2 == $nn_vendor_script->get_payment_type_level() ) { //CreditEntry payment and Collections available
                    //Credit entry of INVOICE or PREPAYMENT
                    if ( 'INVOICE_CREDIT' == $nn_capture_params[ 'payment_type' ] ) {
                        $callback_comments = '';
                        if ( $nn_trans_history[ 'order_paid_amount' ] < $nn_trans_history[ 'total_amount' ] ) {
                            $callback_comments .= 'Novalnet Callback Script executed successfully for the TID: ' . $nn_capture_params[ 'shop_tid' ] . ' with amount:' . GFCommon::to_money( $amount/100, rgar( $nn_capture_params, 'currency' ) ) . ' on ' . date( 'Y-m-d H:i:s' ) . '. Please refer PAID transaction in our Novalnet Merchant Administration with the TID:' . $nn_capture_params[ 'tid' ];
                            $amount = $nn_trans_history[ 'order_paid_amount' ] + $nn_capture_params[ 'amount' ];
                            if ( $nn_trans_history[ 'total_amount' ] <= $amount ) {
                                //Full Payment paid
                                $callback_status_id = !empty( $nn_trans_history[ 'callback_script_status' ] ) ? $nn_trans_history[ 'callback_script_status' ] : 'Pending';
                                $callback_comments .= ( ( $nn_trans_history[ 'order_paid_amount' ] + $nn_capture_params[ 'amount' ] ) > $nn_trans_history[ 'total_amount' ] ) ?' Customer has paid more than the Order amount.' : '';
                                //Update callback order status due to full payment
                                $wpdb->update( $wpdb->prefix.'rg_lead', array( 'payment_status' => $callback_status_id ), array( 'ID' => $order_id ) );
                                $update_status = false;
                            } else {
                                //Partial Payment paid
                                $callback_status_id = $nn_trans_history[ 'order_current_status' ];
                                $update_status = true;
                            }
                            $comments = PHP_EOL . PHP_EOL . $callback_comments ;
                            // Update callback comments in order status history table
                            $nn_vendor_script->update_callback_comments( array( 'order_no' => $order_id, 'comments' => $comments, 'orders_status_id' => $callback_status_id ), $update_status );
                            //Send notification mail to Merchant
                            $request_amount = $nn_capture_params[ 'amount' ];
                            $class_name = $nn_trans_history[ 'class_name' ];
                            $total_amount = $nn_trans_history[ 'total_amount' ];
                            $nn_vendor_script->send_notify_mail( array( 'comments' => $callback_comments, 'order_no' => $order_id ), $ary_capture_params );
                            // Log callback process ( for all types of payments default )
                            $nn_vendor_script->log_callback_process( $nn_trans_history, $nn_capture_params, $order_id );
                            $nn_vendor_script->debug_error( $callback_comments );
                        }
                        $nn_vendor_script->debug_error( 'Novalnet callback received. Callback Script executed already. Refer Order :' . $order_id );
                    }
                    $error = 'Payment type ( ' . $nn_capture_params[ 'payment_type' ] . ' ) is not applicable for this process!';
                    $nn_vendor_script->debug_error( $error );
                } else if ( 1 == $nn_vendor_script->get_payment_type_level() ) { //level 1 payments - Type of Chargebacks
                    // DO THE STEPS TO UPDATE THE STATUS OF THE ORDER OR THE USER AND NOTE THAT THE PAYMENT WAS RECLAIMED FROM USER
                    // Update callback comments in order status history table
                    $callback_comments ='Novalnet callback received. Chargeback was executed for the TID:' . $nn_capture_params[ 'tid_payment' ] . ' amount : ' . GFCommon::to_money( $amount/100, rgar( $nn_capture_params, 'currency' ) )  . ' on ' . date( "Y-m-d H:i:s" ). '. The subsequent TID : ' . $nn_capture_params[ 'tid' ];
                    $callback_status_id = $nn_trans_history[ 'order_current_status' ];
                    $comments = PHP_EOL . $callback_comments ;
                    $nn_vendor_script->update_callback_comments( array( 'order_no' => $order_id, 'comments' => $comments, 'orders_status_id' => $callback_status_id ) );
                    //Send notification mail to Merchant
                    $nn_vendor_script->send_notify_mail( array( 'comments' => $callback_comments, 'order_no' => $order_id ), $ary_capture_params );
                    // Log callback process ( for all types of payments default )
                    $nn_vendor_script->log_callback_process( $nn_trans_history, $nn_capture_params, $order_id );
                    $nn_vendor_script->debug_error( $callback_comments );
                }else if ( 0 === $nn_vendor_script->get_payment_type_level() ) { //level 0 payments - Type of payment
                    if ( 'PAYPAL' == $nn_capture_params[ 'payment_type' ] ) {
                        if ( $nn_trans_history[ 'order_paid_amount' ] < $nn_trans_history[ 'total_amount' ] ) {
                            // Update callback order status due to full payment
                            $callback_status_id = $nn_trans_history[ 'callback_script_status' ];
                            $nn_vendor_script->update_callback_comments( array( 'order_no' => $order_id, 'comments' => $comments, 'orders_status_id' => $callback_status_id ), true );
                            $callback_comments = 'Novalnet Callback Script executed successfully for the TID : ' . $nn_capture_params[ 'shop_tid' ] . ' with amount : ' . GFCommon::to_money( $amount/100, rgar( $nn_capture_params, 'currency' ) ) . ' on ' . date( 'Y-m-d H:i:s' ) . '. Please refer PAID transaction in our Novalnet Merchant Administration with the TID : ' . $nn_capture_params[ 'tid' ];
                            $comments = PHP_EOL . $callback_comments ;
                            // Update callback comments in order status history table
                            $nn_vendor_script->update_callback_comments( array( 'order_no' => $order_id, 'comments' => $comments, 'orders_status_id' => $callback_status_id ) );
                            //Send notification mail to Merchant
                            $nn_vendor_script->send_notify_mail( array( 'comments' => $callback_comments, 'order_no' => $order_id ), $ary_capture_params );
                            // Log callback process ( for all types of payments default )
                            $nn_vendor_script->log_callback_process( $nn_trans_history, $nn_capture_params , $order_id );
                            $nn_vendor_script->debug_error( $callback_comments );
                        }
                        $error = 'Novalnet Callbackscript received. Payment type ( ' . $nn_capture_params[ 'payment_type' ] . ' ) is not applicable for this process!';
                        $nn_vendor_script->debug_error( $error );
                    } else {
                      $nn_vendor_script->debug_error( 'Novalnet Callbackscript received. Order already Paid' );
                    }
                } else {
                  $nn_vendor_script->debug_error( 'Novalnet Callbackscript received. Order already Paid' );
                }
                if ( 'SUBSCRIPTION_STOP' == $nn_capture_params[ 'payment_type' ] ) {
                    ### UPDATE THE STATUS OF THE USER SUBSCRIPTION ###
                }
            }
        } else {
            $nn_vendor_script->debug_error( 'Order Reference not exist!' );
        }
    }
}
?>