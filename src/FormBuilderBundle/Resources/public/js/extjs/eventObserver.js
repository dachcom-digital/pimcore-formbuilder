FormbuilderEventObserver = Class.create({

    observerSections: {},

    initialize: function () {
        this.observerSections = {};
    },

    registerObservable: function (observableId) {

        if (this.observerSections.hasOwnProperty(observableId)) {
            return;
        }

        this.observerSections[observableId] = new Ext.util.Observable();
    },

    unregisterObservable: function (observableId) {

        if (!this.observerSections.hasOwnProperty(observableId)) {
            return;
        }

        delete this.observerSections[observableId];
    },

    getObserver: function (observableId) {
        return this.observerSections[observableId];
    }
});
