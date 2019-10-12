export const warPlayground = {
    controller: warPlaygroundController,
    controllerAs: 'vm',
    template:
    '<div class="playground">' +
    '   <div class="col-xs-12 col-md-6">' +
    '       <pl-md-editor value="vm.text" on-change="vm.throttledParse(value, editor)" mde-config="vm.options" on-search="vm.search(text)"></pl-md-editor>' +
    '       <pl-entity-autosave value="vm.text" entity-type="\'playground\'" entity-id="0" on-load="vm.loadSave(text)" auto-load="true"></pl-entity-autosave>' +
    '   </div>' +
    '   <div class="col-xs-12 col-md-6 article" ng-bind-html="vm.result"></div>' +
    '</div>',
    bindings: {
        afterParse: '&?',
        afterSearch: '&?'
    }
};

warPlaygroundController.$inject = ['mdEditorService', '$sce', 'plEntityService', 'plDataService'];
function warPlaygroundController(mdEditorService, $sce, plEntityService, plDataService) {
    /* jshint validthis: true */
    let vm = this,
        parser = plDataService.throttle(_parse, 2500);

    vm.text = '';
    vm.result = '';
    vm.options = {};

    vm.$onInit = activate;
    vm.loadSave = loadSave;
    vm.search = search;
    vm.throttledParse = throttledParse;

    ////////////////

    function activate() {
        let buttons = mdEditorService.defaultButtons;

        buttons.push(
            mdEditorService.tagWrapButton('bluepost', 'fa fa-quote-right fa-blue', 'bluepost', 'Цитата Blizzard (блюпост)')
        );

        buttons.push(
            mdEditorService.wrapButton('quotes', 'fa fa-angle-double-right', '«', '»', 'Кавычки')
        );

        vm.options = {
            autofocus: true,
            plasticode: {
                preview: true
            },
            toolbar: buttons
        };
    }

    function loadSave(text) {
        vm.text = text;
    }

    function throttledParse(text, editor) {
        vm.text = text;
        return parser(text)
    }

    function search(text) {
        return plDataService.getMdeSearchHints(text).then((results) => {
            if(vm.afterSearch) {
                vm.afterSearch({results: results});
            }
            return results
        });
    }

    function _parse(text) {
        return plEntityService.parse(text).then(resp => {
            vm.result = $sce.trustAsHtml(resp.text);
            if(vm.afterParse) {
                vm.afterParse({result: vm.result});
            }
        })
    }
}