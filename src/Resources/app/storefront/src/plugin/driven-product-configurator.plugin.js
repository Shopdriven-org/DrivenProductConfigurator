import Plugin from 'src/plugin-system/plugin.class';

export default class DrivenProductConfiguratorPlugin extends Plugin {
 init() {
     // bind events to every element
     this.bindEvents(this.el);
 }

    bindEvents(el) {
        $('.driven_racquet_variant').on('change', function (e) {
            var optionSelected = $("option:selected", this);
            var valueSelected = this.value;
            //  TODO : MAKE AJAX REQUEST
        });
    }
}