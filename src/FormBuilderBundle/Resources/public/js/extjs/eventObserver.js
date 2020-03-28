FormbuilderEventObserver = Class.create({

    observerSections: {},

    initialize: function () {
        this.observerSections = {};
    },

    registerForm: function (formId) {

        if (this.observerSections.hasOwnProperty(formId)) {
            return;
        }

        this.observerSections[formId] = new Ext.util.Observable();
    },

    unregisterForm: function (formId) {

        if (!this.observerSections.hasOwnProperty(formId)) {
            return;
        }

        delete this.observerSections[formId];
    },

    getObserver: function (formId) {
        return this.observerSections[formId];
    }
});
