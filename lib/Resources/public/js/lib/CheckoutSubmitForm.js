/*
    This is a special implementation of the CheckoutForm
    for checkouts which have only PayPal as payment method.
*/

var origFunc = tx.app.CheckoutSubmitForm.prototype.getValues;

tx.app.CheckoutSubmitForm.prototype.getValues = function()
{
    var values = origFunc.call(this);

    values.payment = { module: "pp", method : "pp", account : {} };

    return values;
};

/* replacement for the submit area */

tx.app.CheckoutFormSubmitBlock.prototype.nodify = function()
{
    this.extend(this, ag.ui.tool.tpl("tx-paypal", ".paypal-submit"));
    this.find(".submit").html(ag.ui.tool.tpl("tx-app-checkout", ".submit *"));
    this.find(".logo").html(ag.ui.tool.tpl("tx-paypal", ".paypal-logo"));

    this.price = this.find(".price");
};
