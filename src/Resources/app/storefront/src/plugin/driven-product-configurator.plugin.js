import Plugin from 'src/plugin-system/plugin.class';

export default class DrivenProductConfiguratorPlugin extends Plugin {
 init() {

     function selectChange(el) {

         $('#driven-checkout-selection').submit();
         console.log("dsffdsfsdfsdfd")
     }


     // $('.driven_racquet_variant').on('change', function (e) {
     //     var optionSelected = $("option:selected", this);
     //     var valueSelected = this.value;
     //     //  TODO : MAKE AJAX REQUEST
     //
     //     $.ajax
     //     ({
     //         url: 'reservebook.php',
     //         data: {"bookID": book_id},
     //         type: 'post',
     //         success: function(result)
     //         {
     //             $('.modal-box').text(result).fadeIn(700, function()
     //             {
     //                 setTimeout(function()
     //                 {
     //                     $('.modal-box').fadeOut();
     //                 }, 2000);
     //             });
     //         }
     //     });
     // });
 }
}