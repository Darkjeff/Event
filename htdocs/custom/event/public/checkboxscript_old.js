let userUnit, sendEventTop, sendEventBottom, daysCheckbox, minPrice, btnCours;
let pricesArray = [];
let cout_total = 0;

function userHaveCredit(){
    daysCheckbox.each(function( key, value ) {
        //console.log( $(this).data("price") );
        pricesArray.push($(this).data("price"));
    });

    minPrice = Math.min.apply(Math,pricesArray);
    //console.log(userUnit >= minPrice);
    return userUnit >= minPrice;
}

function disableReservation(){
    sendEventTop.val('PAS ASSEZ D\'UNITES');
    sendEventBottom.val('PAS ASSEZ D\'UNITES');
    sendEventTop.prop('disabled', true);
    sendEventBottom.prop('disabled', true);
    sendEventTop.css('cursor', 'not-allowed');
    sendEventBottom.css('cursor', 'not-allowed');

    $(".label-cours").find("*").prop("disabled", true);
    $(".label-cours").find("*").addClass("disabled");

    $(".desinscrire").find("*").prop("disabled", false);
    $(".desinscrire").find("*").removeClass("disabled");

}

function enableReservation(){
    sendEventTop.val('RÉSERVEZ VOS COURS');
    sendEventBottom.val('RÉSERVEZ VOS COURS');
    sendEventTop.prop('disabled', false);
    sendEventBottom.prop('disabled', false);
    sendEventTop.css('cursor', 'pointer');
    sendEventBottom.css('cursor', 'pointer');

    $(".label-cours").find("*").prop("disabled", false);
    $(".label-cours").find("*").removeClass("disabled");

    $(".desinscrire").find("*").prop("disabled", false);
    $(".desinscrire").find("*").removeClass("disabled");
}

function allowCancel(){
    sendEventTop.val('ANNULER RESERVATION');
    sendEventBottom.val('ANNULER RESERVATION');
    sendEventTop.prop('disabled', false);
    sendEventBottom.prop('disabled', false);
    sendEventTop.css('cursor', 'pointer');
    sendEventBottom.css('cursor', 'pointer');
}



$(document).ready(function() {

    userUnit = parseInt($('#nb_unit').text());
    sendEventTop = $("#send_event_top");
    sendEventBottom = $('#send_event_bot');
    daysCheckbox = $(".checkbox_day");
    btnCours = $(".btn-cours");


    if (userHaveCredit() === false){
      disableReservation();
    }


$('#formevent').submit(function(){
    //console.log("#formevent.submit()");
  var ok = 0;
  var ok_change = 0;
  var nb_unit = parseInt($('#nb_unit').text());
  var cout_total = parseInt($('#totval').text());
  $('input[type=checkbox]').each(function () {
             if (this.checked) {
                    ok = 1;
             }
             if ($(this).hasClass("change")){
               ok_change = 1;
             }
  });
  if ((ok == 1 && ok_change == 1) || ok_change == 1){
    $('.send_event').prop('disabled', false);
    $('.send_event').css('cursor', 'pointer');
    $('.send_error').hide("fast");
  }
    else {
      $('.send_event').prop('disabled', true);
      $('.send_event').css('cursor', 'not-allowed');
      $('.send_error').show("fast");
      return false;
    }
    if (cout_total > nb_unit)
    {
      $('#send_event_top').val('PAS ASSEZ D\'UNITES');
      $('#send_event_bot').val('PAS ASSEZ D\'UNITES');
      $('#send_event_top').prop('disabled', true);
      $('#send_event_bot').prop('disabled', true);
      return false;
    }
    else{
      $('#send_event_top').val('RÉSERVEZ VOS COURS');
      $('#send_event_bot').val('RÉSERVEZ VOS COURS');
      $('#send_event').prop('disabled', false);
      $('#send_event').prop('disabled', false);
    }
});



$(':checkbox').change(function() {
    //console.log(":checkbox.change()");

    var ok = 0;
    var ok_change = 0;
    //var cout_total = 0;
    var nb_unit = parseInt($('#nb_unit').text());

    if ($(this).hasClass("change"))
        $(this).removeClass("change");
    else
    $(this).addClass("change");

    if ($(this).prop('checked')){

        if( $(this).hasClass('payed')){
            //console.log("checked and payed");
            cout_total -= parseInt($(this).data("price"));
            userUnit += parseInt($(this).data("price"));
        }else{
            //console.log("checked and NOT payed");
            cout_total += parseInt($(this).data("price"));
            userUnit -= parseInt($(this).data("price"));
        }
    }else{
        if( $(this).hasClass('payed')){
            //console.log("UNchecked and payed");
            cout_total += parseInt($(this).data("price"));
            userUnit -= parseInt($(this).data("price"));
        }else{
            //console.log("UNchecked and NOT payed");
            cout_total -= parseInt($(this).data("price"));
            userUnit += parseInt($(this).data("price"));
        }

    }

    //console.log(userUnit);
    if(userHaveCredit() === true){
        //console.log("User have credit");
        enableReservation();
    }else{
        //console.log("User have NO credit");
        $(".label-cours").find("input[type=checkbox]:not(:checked)").each(function(){
              //console.log($(this));

              if (!$(this).hasClass("payed")){
                  $(this).prop("disabled", true);
                  $(this).addClass("disabled");
                  $(this).parent().prop("disabled", true);
                  $(this).parent().addClass("disabled");
                  $(this).parent().parent().prop("disabled", true);
                  $(this).parent().parent().addClass("disabled");

              }
          });




        //$(".label-cours").find("*").addClass("disabled");



        //disableReservation();
    }


    $(".totalvalue").text(cout_total);

    /*    $('input[type=checkbox]').each(function () {
               if (this.checked && !($(this).hasClass("payed"))) {
                   console.log("NOT payed");
                   ok = 1;
                   cout_total += parseInt($(this).attr("data-price"));
               }else{
                   console.log("payed");
                   cout_total -= parseInt($(this).attr("data-price"));
               }
              if ($(this).hasClass("change")){
                ok_change = 1;
              }

        });*/

/*$(".totalvalue").text(cout_total);

if ((ok === 1 && ok_change === 1) || ok_change === 1){

    console.log("Enter OK change");
    $('.send_event').prop('disabled', false);
    $('.send_event').css('cursor', 'pointer');
    $('.send_error').hide("fast");
    allowCancel();
}else {

    if (userHaveCredit() === true ){
        enableReservation();
    }else {
        disableReservation();
    }*/


/*    $('.send_event').prop('disabled', true);
    $('.send_event').css('cursor', 'not-allowed');
    $('.send_error').show("fast");*/
//}





/*  if (cout_total > nb_unit)
  {
    $('#send_event_top').val('PAS ASSEZ D\'UNITES');
    $('#send_event_bot').val('PAS ASSEZ D\'UNITES');
  }
  else{
    $('#send_event_top').val('RÉSERVEZ VOS COURS');
    $('#send_event_bot').val('RÉSERVEZ VOS COURS');
  }*/

});
});
