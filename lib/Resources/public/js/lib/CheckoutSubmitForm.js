ag.ns("ag.paypal");

(function(){

/*
    This is a special implementation of the CheckoutForm
    for checkouts which have only PayPal as payment method.

    NOTE: DO not add the CheckoutFormPaymentBlock or the CheckoutFormSubmitBlock to the blocks map.
*/
var CheckoutSubmitForm = function(blocks)
{
    ag.order.CheckoutSubmitForm.call(this, blocks);
};

CheckoutSubmitForm.prototype = Object.create(ag.order.CheckoutSubmitForm.prototype);

CheckoutSubmitForm.prototype.getValues = function()
{
    var values = ag.order.CheckoutSubmitForm.prototype.getValues.call(this);

    values.payment = { module: "pp", method : "pp", account : {} };

    return values;
};

ag.paypal.CheckoutSubmitForm = CheckoutSubmitForm;




/* replacement for the submit area */

var CheckoutFormSubmitBlock = function()
{
    ag.order.CheckoutFormSubmitBlock.apply(this, arguments);
};

CheckoutFormSubmitBlock.prototype = Object.create(ag.order.CheckoutFormSubmitBlock.prototype);

CheckoutFormSubmitBlock.prototype.nodify = function()
{
    this.extend(this, ag.ui.tool.tpl("agit-paypal", ".paypal-submit"));
    this.find(".submit").html(ag.ui.tool.tpl("agit-order-checkout", ".submit *"));
    this.find(".logo").html(ag.ui.tool.tpl("agit-paypal", ".paypal-logo"));

    this.price = this.find(".price");
};

ag.paypal.CheckoutFormSubmitBlock = CheckoutFormSubmitBlock;



})();
