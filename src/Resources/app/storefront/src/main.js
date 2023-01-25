// import
import PluginManager from 'src/plugin-system/plugin.manager';
import DrivenProductConfiguratorPlugin from "./plugin/driven-product-configurator.plugin";


$(document).ready(function(){
    $('.driven-equipments').on('change', function(event ) {
        //restore previously selected value
        var prevValue = $(this).data('previous');
        $('.driven-equipments').not(this).find('option[value="'+prevValue+'"]').show();
        //hide option selected
        var value = $(this).val();
        //update previously selected data
        $(this).data('previous',value);
        $('.driven-equipments').not(this).find('option[value="'+value+'"]').hide();
    });
});

// register plugin
PluginManager.register(
    'DrivenProductConfiguratorPlugin',
    DrivenProductConfiguratorPlugin,
    '[data-driven-product-configurator]'
);