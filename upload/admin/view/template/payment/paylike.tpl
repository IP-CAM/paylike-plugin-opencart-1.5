<?php echo $header; ?>
<div id="content">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a
                href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>
    <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
    <?php } ?>
    <div class="box">
        <div class="heading">
            <h1><?php echo $heading_title; ?></h1>
            <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a
                        onclick="location = '<?php echo $cancel; ?>';" class="button"><?php echo $button_cancel; ?></a>
            </div>
        </div>
        <div class="content">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                <table class="form">
                    <tr>
                        <td> <?php echo $payment_method_title; ?></td>
                        <td>
                            <input type="text" name="paylike_payment_method_title"
                                   value="<?php echo ($paylike_payment_method_title)?$paylike_payment_method_title:$default_payment_method_title; ?>"
                                   placeholder="<?php echo $payment_method_title; ?>" id="input-payment-method-title"
                                   size="100"/>
                            <?php if ($error_payment_method_title) { ?>
                            <span class="error"><?php echo $error_payment_method_title; ?></span>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td> <?php echo $payment_method_description; ?></td>
                        <td>
                            <input type="text" name="paylike_payment_method_description"
                                   value="<?php echo ($paylike_payment_method_description)?$paylike_payment_method_description:$default_payment_method_description; ?>"
                                   placeholder="<?php echo $payment_method_description; ?>"
                                   id="input-payment-method-description" size="100"/>
                            <?php if ($error_payment_method_description) { ?>
                            <span class="error"><?php echo $error_payment_method_description; ?></span>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td> <?php echo $entry_title; ?></td>
                        <td>
                            <input type="text" name="paylike_title"
                                   value="<?php echo ($paylike_title)?$paylike_title:$this->config->get('config_name'); ?>"
                                   placeholder="<?php echo $entry_title; ?>" id="input-title" size="100"/>
                            <?php if ($error_title) { ?>
                            <span class="error"><?php echo $error_title; ?></span>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td> <?php echo $description_status; ?></td>
                        <td>
                            <select name="paylike_description_status" id="input-description-status">
                                <option value="0"
                                <?php echo (!$paylike_description_status)?'selected="selected"':'';?>
                                ><?php echo $text_no; ?></option>
                                <option value="1"
                                <?php echo ($paylike_description_status)?'selected="selected"':'';?>
                                ><?php echo $text_yes; ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td> <?php echo $entry_description; ?></td>
                        <td>
                            <input type="text" name="paylike_description"
                                   value="<?php echo ($paylike_description)?$paylike_description:$default_entry_description; ?>"
                                   placeholder="<?php echo $paylike_description; ?>" id="input-description" size="100"/>
                        </td>
                    </tr>
                    <tr>
                        <td> <?php echo $entry_mode; ?></td>
                        <td>
                            <select name="paylike_mode" id="input-mode" class="form-control">
                                <option value="live"
                                <?php echo ($paylike_mode=='live')?'selected="selected"':'';?>
                                ><?php echo $text_live; ?></option>
                                <option value="test"
                                <?php echo ($paylike_mode=='test')?'selected="selected"':'';?>
                                ><?php echo $text_test; ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td> <?php echo $entry_test_key; ?></td>
                        <td>
                            <input type="text" name="paylike_test_key" value="<?php echo $paylike_test_key; ?>"
                                   placeholder="<?php echo $entry_test_key; ?>" id="input-test-key" size="50"/>
                            <?php if ($error_test_key) { ?>
                            <span class="error"><?php echo $error_test_key; ?></span>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td> <?php echo $entry_test_app_key; ?></td>
                        <td>
                            <input type="text" name="paylike_test_app_key" value="<?php echo $paylike_test_app_key; ?>"
                                   placeholder="<?php echo $entry_test_app_key; ?>" id="input-test-app-key" size="50"/>
                            <?php if ($error_test_app_key) { ?>
                            <span class="error"><?php echo $error_test_app_key; ?></span>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td> <?php echo $entry_live_key; ?></td>
                        <td>
                            <input type="text" name="paylike_live_key" value="<?php echo $paylike_live_key; ?>"
                                   placeholder="<?php echo $entry_live_key; ?>" id="input-live-key" size="50"/>
                            <?php if ($error_live_key) { ?>
                            <span class="error"><?php echo $error_live_key; ?></span>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td> <?php echo $entry_live_app_key; ?></td>
                        <td>
                            <input type="text" name="paylike_live_app_key" value="<?php echo $paylike_live_app_key; ?>"
                                   placeholder="<?php echo $entry_live_app_key; ?>" id="input-live-app-key" size="50"/>
                            <?php if ($error_live_app_key) { ?>
                            <span class="error"><?php echo $error_live_app_key; ?></span>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr style="display: none;">
                        <td> <?php echo $entry_total; ?></td>
                        <td>
                            <input type="text" name="paylike_total"
                                   value="<?php echo ($paylike_total)?$paylike_total:1; ?>"
                                   placeholder="<?php echo $entry_total; ?>" id="input-total" size="50"/>
                        </td>
                    </tr>
                    <tr>
                        <td> <?php echo $entry_order_status; ?></td>
                        <td>
                            <select name="paylike_order_status_id" id="input-order-status" class="form-control">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status[ 'order_status_id'] == $paylike_order_status_id) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected">
                                    <?php echo $order_status[ 'name']; ?>
                                </option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>">
                                    <?php echo $order_status[ 'name']; ?>
                                </option>
                                <?php } ?>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td> <?php echo $entry_capture; ?></td>
                        <td>
                            <select name="paylike_capture" id="input-capture" class="form-control">
                                <option value="2"
                                <?php echo ((!isset($paylike_capture)||$paylike_capture==null)||($paylike_capture=='2'))?'selected="selected"':'';?>
                                ><?php echo $text_capture_delayed; ?></option>
                                <option value="1"
                                <?php echo ($paylike_capture=='1')?'selected="selected"':'';?>
                                ><?php echo $text_capture_instant; ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td> <?php echo $entry_geo_zone; ?></td>
                        <td>
                            <select name="paylike_geo_zone_id" id="input-geo-zone" class="form-control">
                                <option value="0">
                                    <?php echo $text_all_zones; ?>
                                </option>
                                <?php foreach ($geo_zones as $geo_zone) { ?>
                                <?php if ($geo_zone[ 'geo_zone_id'] == $paylike_geo_zone_id) { ?>
                                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected">
                                    <?php echo $geo_zone[ 'name']; ?>
                                </option>
                                <?php } else { ?>
                                <option value="<?php echo $geo_zone['geo_zone_id']; ?>">
                                    <?php echo $geo_zone[ 'name']; ?>
                                </option>
                                <?php } ?>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td> <?php echo $entry_status; ?></td>
                        <td>
                            <select name="paylike_status" id="input-status" class="form-control">
                                <option value="1"
                                <?php echo ($paylike_status)?'selected="selected"':'';?>
                                ><?php echo $text_enabled; ?></option>
                                <option value="0"
                                <?php echo (!$paylike_status)?'selected="selected"':'';?>
                                ><?php echo $text_disabled; ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td> <?php echo $entry_sort_order; ?></td>
                        <td>
                            <input type="text" name="paylike_sort_order" value="<?php echo $paylike_sort_order; ?>"
                                   placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order"/>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</div>
<?php echo $footer; ?>
