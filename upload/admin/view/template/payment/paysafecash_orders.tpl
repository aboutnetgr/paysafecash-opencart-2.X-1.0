<?php echo $header ?><?php echo $column_left ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="button" data-toggle="tooltip" title="<?php echo $button_refund ?>" class="btn btn-danger" onclick="start_refund();"><i class="fa fa-cogs"></i></button>
            </div>
            <h1><?php echo $heading_title ?></h1>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <?php if ($error_install) { ?>
        <div class="alert alert-danger"><i class="fa fa-check-circle"></i> <?php echo $error_install; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } else { ?>
            <?php if ($success) { ?>
            <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php } elseif (isset($referror)) { ?>
            <div class="alert alert-danger"><i class="fa fa-check-circle"></i> <?php echo $referror; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php } ?>
            <?php
            if ($refund_messages) {
                foreach ($refund_messages as $msg) {
                    if ($msg['status'] == 1) {
                        echo '<div class="alert alert-success"><i class="fa fa-check-circle"></i> '.$entry_order_id.': '.$msg['order_id'].' '.$msg['message'].' <button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                    } else {
                        echo '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> '.$entry_order_id.': '.$msg['order_id'].' '.$msg['message'].' ('.$msg['number'].') <button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                    }
                }
            }
            ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $text_list; ?></h3>
                </div>
                <div class="panel-body">
                    <div class="well">
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="input-order-id"><?php echo $entry_order_id; ?></label>
                                    <input type="text" name="filter_order_id" value="<?php echo $filter_order_id; ?>" placeholder="<?php echo $entry_order_id; ?>" id="input-order-id" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <label class="control-label" for="input-customer"><?php echo $entry_customer; ?></label>
                                    <input type="text" name="filter_customer" value="<?php echo $filter_customer; ?>" placeholder="<?php echo $entry_customer; ?>" id="input-customer" class="form-control" />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="input-paymentid"><?php echo $entry_paymentid; ?></label>
                                    <input type="text" name="filter_paymentid" value="<?php echo $filter_paymentid; ?>" id="input-paymentid" class="form-control" />
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="input-order-status"><?php echo $entry_orderstatus; ?></label>
                                    <select name="filter_order_status" id="input-order-status" class="form-control">
                                        <option value="*"></option>
                                        <?php if ($filter_order_status == '0') { ?>
                                        <option value="0" selected="selected"><?php echo $text_missing; ?></option>
                                        <?php } else { ?>
                                        <option value="0"><?php echo $text_missing; ?></option>
                                        <?php } ?>
                                        <?php foreach ($order_statuses as $order_status) { ?>
                                        <?php if ($order_status['order_status_id'] == $filter_order_status) { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="input-date-added"><?php echo $entry_date_added; ?></label>
                                    <div class="input-group date">
                                        <input type="text" name="filter_date_added" value="<?php echo $filter_date_added; ?>" placeholder="<?php echo $entry_date_added; ?>" data-date-format="YYYY-MM-DD" id="input-date-added" class="form-control" />
                                        <span class="input-group-btn">
                                        <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label" for="input-date-modified"><?php echo $entry_date_modified; ?></label>
                                    <div class="input-group date">
                                        <input type="text" name="filter_date_modified" value="<?php echo $filter_date_modified; ?>" placeholder="<?php echo $entry_date_modified; ?>" data-date-format="YYYY-MM-DD" id="input-date-modified" class="form-control" />
                                        <span class="input-group-btn">
                                        <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="button-filter" class="btn btn-primary pull-right"><i class="fa fa-filter"></i> <?php echo $button_filter; ?></button>
                        </div>
                    </div>
                    <form action="<?php echo $refund; ?>" method="post" enctype="multipart/form-data" id="form-refund">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <td style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('input[name*=\'selected_orders\']').prop('checked', this.checked);" /></td>

                                        <td class="text-left"><?php if ($sort == 'order_id') { ?>
                                        <a href="<?php echo $sort_order; ?>" class="<?php echo strtolower($order); ?>"><?php echo $col_order; ?></a>
                                        <?php } else { ?>
                                        <a href="<?php echo $sort_order; ?>"><?php echo $col_order; ?></a>
                                        <?php } ?></td>
                                        <td class="text-left"><?php echo $col_paymentid; ?></td>
                                        <td class="text-left"><?php echo $col_customer; ?></td>
                                        <td class="text-left"><?php if ($sort == 'status') { ?>
                                        <a href="<?php echo $sort_status; ?>" class="<?php echo strtolower($order); ?>"><?php echo $col_status; ?></a>
                                        <?php } else { ?>
                                        <a href="<?php echo $sort_status; ?>"><?php echo $col_status; ?></a>
                                        <?php } ?></td>
                                        <td class="text-left"><?php echo $col_total; ?></td>
                                        <td class="text-left"><?php echo $col_date_added; ?></td>
                                        <td class="text-left"><?php echo $col_date_modified; ?></td>
                                        <td class="text-left"><?php echo $col_refunded; ?></td>
                                        <td class="text-left"><?php echo $col_refunddate; ?></td>
                                        <td class="text-right"><?php echo $col_view; ?></td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($psorders) { ?>
                                    <?php foreach ($psorders as $psorder) { ?>
                                    <tr>
                                        <td class="text-center"><?php if (in_array($psorder['order_id'], $selected_orders)) { ?>
                                            <input type="checkbox" name="selected_orders[]" value="<?php echo $psorder['order_id']; ?>" checked="checked" />
                                            <?php } else { ?>
                                            <input type="checkbox" name="selected_orders[]" value="<?php echo $psorder['order_id']; ?>" />
                                            <?php } ?></td>
                                        <td class="text-left"><?php echo $psorder['order_id']; ?></td>
                                        <td class="text-left"><?php echo $psorder['payment_id']; ?></td>
                                        <td class="text-left"><?php echo $psorder['customer']; ?></td>
                                        <td class="text-left"><?php echo $psorder['status']; ?></td>
                                        <td class="text-left"><?php echo $psorder['total']; ?></td>
                                        <td class="text-left"><?php echo $psorder['date_added']; ?></td>
                                        <td class="text-left"><?php echo $psorder['date_modified']; ?></td>
                                        <td class="text-left"><?php echo($psorder['refunded'] == 1 ? $text_yes : ''); ?></td>
                                        <td class="text-left"><?php echo($psorder['refunded'] == 1 ? $psorder['refunded_date'] : ''); ?></td>
                                        <td class="text-right"><a href="<?php echo $psorder['view']; ?>" data-toggle="tooltip" title="<?php echo $button_view; ?>" class="btn btn-info"><i class="fa fa-eye"></i></a></td>
                                    </tr>
                                    <?php } ?>
                                    <?php } else { ?>
                                    <tr>
                                        <td class="text-center" colspan="11"><?php echo $text_no_results; ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                    <div class="row">
                        <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
                        <div class="col-sm-6 text-right"><?php echo $results; ?></div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<script type="text/javascript"><!--
$('#button-filter').on('click', function() {
    var url = 'index.php?route=payment/paysafecash/listorders&token=<?php echo $token; ?>';
    var filter_order_id = $('input[name=\'filter_order_id\']').val();
    if (filter_order_id) {
        url += '&filter_order_id=' + encodeURIComponent(filter_order_id);
    }

    var filter_customer = $('input[name=\'filter_customer\']').val();

    if (filter_customer) {
        url += '&filter_customer=' + encodeURIComponent(filter_customer);
    }

    var filter_paymentid = $('input[name=\'filter_paymentid\']').val();

    if (filter_paymentid) {
        url += '&filter_paymentid=' + encodeURIComponent(filter_paymentid);
    }

    var filter_order_status = $('select[name=\'filter_order_status\']').val();

    if (filter_order_status != '*') {
        url += '&filter_order_status=' + encodeURIComponent(filter_order_status);
    }

    var filter_date_added = $('input[name=\'filter_date_added\']').val();

    if (filter_date_added) {
        url += '&filter_date_added=' + encodeURIComponent(filter_date_added);
    }

    var filter_date_modified = $('input[name=\'filter_date_modified\']').val();

    if (filter_date_modified) {
        url += '&filter_date_modified=' + encodeURIComponent(filter_date_modified);
    }

    location = url;
});

function start_refund(){
    if (confirm("Are you sure?")) {
        $('#form-refund').submit();
    }
}
//--></script>
<script src="view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<link href="view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css" type="text/css" rel="stylesheet" media="screen" />
<script type="text/javascript"><!--
$('.date').datetimepicker({
    pickTime: false
});
//--></script>

<?php echo $footer; ?>
