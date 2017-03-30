ag.ns("ag.paypal");

(function(){

// form

var paypalForm = function()
{
    this.extend(this, ag.ui.tool.tpl("agit-paypal", "tbody"));
};

paypalForm.prototype = Object.create(ag.order.CheckoutPaymentForm.prototype);

paypalForm.prototype.name = "PayPal";



// payment module

var paypalPaymentModule = function() { };

paypalPaymentModule.prototype = Object.create(ag.order.PaymentModule.prototype);

paypalPaymentModule.prototype.id = "pp";

paypalPaymentModule.prototype.createForm = function()
{
    return new paypalForm();
};

ag.paypal.PaymentModule = paypalPaymentModule;
})();
