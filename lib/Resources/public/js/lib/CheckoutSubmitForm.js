/*
    This is a special implementation of the CheckoutForm
    for checkouts which have only PayPal as payment method.
*/

var origFunc = ag.order.CheckoutSubmitForm.prototype.getValues;

ag.order.CheckoutSubmitForm.prototype.getValues = function()
{
    var values = origFunc.call(this);

    values.payment = { module: "pp", method : "pp", account : {} };

    return values;
};

/* replacement for the submit area */

ag.order.CheckoutFormSubmitBlock.prototype.nodify = function()
{
    this.extend(this, ag.ui.tool.tpl("agit-paypal", ".paypal-submit"));
    this.find(".submit").html(ag.ui.tool.tpl("agit-order-checkout", ".submit *"));
    this.find(".logo").html(ag.ui.tool.tpl("agit-paypal", ".paypal-logo"));

    this.price = this.find(".price");
};
