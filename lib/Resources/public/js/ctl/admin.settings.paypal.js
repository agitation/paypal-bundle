$(document).ready(function(){

var page = new ag.app.Page(
        ag.srv("nav"),
        new ag.ui.ctxt.Views({
        settings : new ag.app.View({
            title : new ag.app.Title(ag.intl.t("PayPal settings")),
            form : new ag.admin.SettingsForm({
                "tixys.payment.paypal.active" : new ag.admin.SettingField(
                    ag.intl.t("Active"),
                    new ag.ui.field.Boolean()
                ),

                "tixys.payment.paypal.environment" : new ag.admin.SettingField(
                    ag.intl.t("Environment"),
                    new ag.ui.field.Select(null, null, [
                        { value : "test", text : ag.intl.x("payment environment", "Test") },
                        { value : "live", text : ag.intl.x("payment environment", "Live") }
                    ])
                ),

                "tixys.payment.paypal.api_username" : new ag.admin.SettingField(
                    ag.intl.t("API username"),
                    new ag.ui.field.Text()
                ),

                "tixys.payment.paypal.api_password" : new ag.admin.SettingField(
                    ag.intl.t("API password"),
                    new ag.ui.field.Text()
                ),

                "tixys.payment.paypal.api_signature" : new ag.admin.SettingField(
                    ag.intl.t("API signature"),
                    new ag.ui.field.Text()
                )
            })
        })
    })
);

page.initialize();

});
