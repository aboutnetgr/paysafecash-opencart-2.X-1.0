
<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <ul class="breadcrumb">
       <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
    </ul>
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-payment" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
            <h1><i class="fa fa-credit-card"></i><?php echo $heading_title; ?></h1>
        </div>
    </div>
    <div class="container-fluid">
        <div class="panel-body">
            <?php if ($error_warning) { ?>
                <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i><?php echo $error_warning; ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php } ?>
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-payment" class="form-horizontal">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $tab_settings; ?></a></li>
                    <li ><a href="#tab-about" data-toggle="tab"><?php echo $tab_about; ?></a></li>
                </ul>
                <div class="tab-content">

                    <div class="tab-pane active" id="tab-general">

                        <div class="form-group">
                            <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="" data-original-title="<?php echo $entry_paysafecash_test_mode; ?>"><?php echo $text_paysafecash_test_mode; ?></span></label>
                            <div class="col-sm-10">
                                <?php if ($paysafecash_test_mode) { ?>
                                    <label class="radio-inline"><input type="radio" name="paysafecash_test_mode" value="1" checked="checked"> <?php echo $text_yes; ?></label>
                                    <label class="radio-inline"><input type="radio" name="paysafecash_test_mode" value="0"> <?php echo $text_no; ?></label>
                                 <?php } else { ?>
                                    <label class="radio-inline"><input type="radio" name="paysafecash_test_mode" value="1" > <?php echo $text_yes; ?></label>
                                    <label class="radio-inline"><input type="radio" name="paysafecash_test_mode" value="0" checked="checked"> <?php echo $text_no; ?></label>
                                <?php } ?>

                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="paysafecash_api_key"><span data-toggle="tooltip" title="" data-original-title="<?php echo $entry_paysafecash_api_key; ?>"><?php echo $text_paysafecash_api_key; ?></span></label>
                            <div class="col-sm-10">
                                <input type="text" name="paysafecash_api_key" value="<?php echo $paysafecash_api_key; ?>"  class="form-control" id="paysafecash_api_key" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="paysafecash_webhook_rsa_key"><span data-toggle="tooltip" title="" data-original-title="<?php echo $entry_paysafecash_webhook_rsa_key; ?>"><?php echo $text_paysafecash_webhook_rsa_key; ?></span></label>
                            <div class="col-sm-10">
                                <textarea name="paysafecash_webhook_rsa_key" class="form-control" id="paysafecash_webhook_rsa_key" cols="20" rows="5"><?php echo $paysafecash_webhook_rsa_key; ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="paysafecash_var_trans_timeout"><span data-toggle="tooltip" title="" data-original-title="<?php echo $entry_paysafecash_var_trans_timeout; ?>"><?php echo $text_paysafecash_var_trans_timeout; ?></span></label>
                            <div class="col-sm-10">
                                <input type="text" name="paysafecash_var_trans_timeout" value="<?php echo $paysafecash_var_trans_timeout; ?>"  class="form-control" id="paysafecash_var_trans_timeout" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="paysafecash_submerchant_id"><span data-toggle="tooltip" title="" data-original-title="<?php echo $entry_paysafecash_submerchant_id; ?>"><?php echo $text_paysafecash_submerchant_id; ?></span></label>
                            <div class="col-sm-10">
                                <input type="text" name="paysafecash_submerchant_id" value="<?php echo $paysafecash_submerchant_id; ?>"  class="form-control" id="paysafecash_submerchant_id" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" ><span data-toggle="tooltip" title="" data-original-title="<?php echo $entry_paysafecash_customer_data; ?>"><?php echo $text_paysafecash_customer_data; ?></span></label>
                            <div class="col-sm-10">
                                <label class="checkbox-inline"><input type="checkbox" name="paysafecash_customer_data" <?php echo($paysafecash_customer_data ? 'checked="checked"' : '') ?> value="1"> <?php echo $text_yes; ?></label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="paysafecash_countries"><span data-toggle="tooltip" title="" data-original-title="<?php echo $entry_paysafecash_countries; ?>"><?php echo $text_paysafecash_countries; ?></span></label>
                            <div class="col-sm-10">
                                <select name="paysafecash_countries[]" multiple="multiple" class="form-control">
                                    <?php foreach ($countries as $country) { ?>
                                    <?php if (in_array($country['iso_code_2'], $paysafecash_countries)) { ?>
                                    <option value="<?php echo $country['iso_code_2']; ?>" selected="selected"><?php echo $country['name']; ?></option>
                                    <?php } else { ?>
                                    <option value="<?php echo $country['iso_code_2']; ?>"><?php echo $country['name']; ?></option>
                                    <?php } ?>
                                    <?php } ?>
                                </select>

                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" ><span data-toggle="tooltip" title="" data-original-title="<?php echo $entry_paysafecash_debug_mode; ?>"><?php echo $text_paysafecash_debug_mode; ?></span></label>
                            <div class="col-sm-10">
                                <label class="checkbox-inline"><input type="checkbox" name="paysafecash_debug_mode" <?php echo($paysafecash_debug_mode ? 'checked="checked"' : '') ?> value="1"> <?php echo $text_paysafecash_enable_debug_mode; ?></label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="input-order-status"><?php echo $entry_order_status; ?></label>
                            <div class="col-sm-10">
                                <select name="paysafecash_order_status_id" id="input-order-status" class="form-control">
                                    <?php  foreach ($order_statuses as $order_status) { ?>
                                        <?php if ($order_status['order_status_id'] ==  $paysafecash_order_status_id) { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        }
                                        <?php } else { ?>
                                         <option value="<?php echo $order_status['order_status_id']; ?>">
                                            <?php echo $order_status['name']; ?></option>
                                       <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="input-declined-order-status"><?php echo $entry_declined_order_status; ?></label>
                            <div class="col-sm-10">
                                <select name="paysafecash_declined_order_status_id" id="input-declined-order-status" class="form-control">
                                    <?php  foreach ($order_statuses as $order_status) { ?>
                                      <?php if ($order_status['order_status_id'] ==  $paysafecash_declined_order_status_id) { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>"> <?php echo $order_status['name']; ?></option>
                                       <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="input-awaiting-order-status"><?php echo $entry_awaiting_order_status; ?></label>
                            <div class="col-sm-10">
                                <select name="paysafecash_awaiting_order_status_id" id="input-awaiting-order-status" class="form-control">
                                    <?php  foreach ($order_statuses as $order_status) { ?>
                                      <?php if ($order_status['order_status_id'] ==  $paysafecash_awaiting_order_status_id) { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>"> <?php echo $order_status['name']; ?></option>
                                       <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="input-awaiting-order-status"><?php echo $entry_refund_order_status; ?></label>
                            <div class="col-sm-10">
                                <select name="paysafecash_refund_order_status_id" id="input-refund-order-status" class="form-control">
                                    <?php  foreach ($order_statuses as $order_status) { ?>
                                      <?php if ($order_status['order_status_id'] ==  $paysafecash_refund_order_status_id) { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>"> <?php echo $order_status['name']; ?></option>
                                       <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>


                        <?php foreach ($languages as $language) { ?>
                        <div class="form-group required">
                            <label class="col-sm-2 control-label" for="input-payment_description<?php echo $language['language_id']; ?>"><?php echo $entry_payment_description; ?></label>
                            <div class="col-sm-10">
                                <div class="input-group"><span class="input-group-addon"><img src="<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                                    <textarea name="paysafecash_payment_description<?php echo $language['language_id']; ?>" cols="80" rows="10" placeholder="<?php echo $entry_payment_description; ?>" id="input-payment_description<?php echo $language['language_id']; ?>" class="form-control"><?php echo isset(${'paysafecash_payment_description'.$language['language_id']}) ? ${'paysafecash_payment_description'.$language['language_id']} : ''; ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="form-group required">
                            <label class="col-sm-2 control-label" for="input-confirm_description<?php echo $language['language_id']; ?>"><?php echo $entry_confirm_description; ?></label>
                            <div class="col-sm-10">
                                <div class="input-group"><span class="input-group-addon"><img src="<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                                    <textarea name="paysafecash_confirm_description<?php echo $language['language_id']; ?>" cols="80" rows="10" placeholder="<?php echo $entry_confirm_description; ?>" id="input-confirm_description<?php echo $language['language_id']; ?>" class="form-control"><?php echo isset(${'paysafecash_confirm_description'.$language['language_id']}) ? ${'paysafecash_confirm_description'.$language['language_id']} : ''; ?></textarea>
                                </div>
                            </div>
                        </div>
                        <?php } ?>


                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                            <div class="col-sm-10">
                                <select name="paysafecash_status" id="input-status" class="form-control">
                                    <?php if ($paysafecash_status) { ?>
                                        <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                        <option value="0"><?php echo $text_disabled; ?></option>
                                   <?php } else { ?>
                                        <option value="1"><?php echo $text_enabled; ?></option>
                                        <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
                            <div class="col-sm-10">
                                <input type="text" name="paysafecash_sort_order" value="<?php echo $paysafecash_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control" />
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="tab-about">
                        <h2><?php echo $text_current_version; ?>: <?php echo $text_current_version_nr; ?></h2>


                        <div id="version-data-checker" style="margin-bottom: 20px">
                            <?php echo $vrs_please_check_version ?>
                        </div>
                        <div style="margin-bottom: 20px">
                            <button type="button" id="version-data-checker-btn" class="btn btn-sm btn-info"><?php echo $button_check ?></button>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
  <script type="text/javascript"><!--
<?php foreach ($languages as $language) { ?>
$('#input-payment_description<?php echo $language['language_id']; ?>').summernote({height: 300});
$('#input-confirm_description<?php echo $language['language_id']; ?>').summernote({height: 300});
<?php } ?>

$("#version-data-checker-btn").on("click", function() {
    $("#version-data-checker").html('<span class="fa fa-cog fa-spin fa-3x fa-fw"></span>');
    $.ajax({type: "GET",
        url: "<?php echo $link_check_version?>",
        dataType: "json",
        success: function(obj){
            if (obj.status == 1) {
                var html = '<p><?php echo $vrs_latest_version ?>: <strong>'+obj.data.latest_version+'</strong></p>';
                html += '<p><?php echo $vrs_last_update ?>: <strong>'+obj.data.lastupdate+'</strong></p>';
                html += '<p><?php echo $vrs_changelog ?><ol>';
                obj.data.changelog.forEach(async function(chng) {
                    html += '<li>'+chng+'</li>';
                });
                html += '</ol></p>';
                $("#version-data-checker").html(html);
            } else {
                alert(obj.msg);
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            $("#version-data-checker").html('<?php echo $vrs_please_check_version ?>');
            alert(textStatus);
        }
    });

    return false;
});

//--></script>
<?php echo $footer; ?>
