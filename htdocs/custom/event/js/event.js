$(document).ready(function() {
    $('#cocheTout').click(function() { // clic sur la case cocher/decocher
           
        var cases = $("#relances").find(':checkbox'); // on cherche les checkbox qui dépendent de la liste 'cases'
        if(this.checked){ // si 'cocheTout' est coché
            cases.attr('checked', true); // on coche les cases
            $('#cocheText').html('Tout décocher'); // mise à jour du texte de cocheText
        }else{ // si on décoche 'cocheTout'
            cases.attr('checked', false);// on coche les cases
            $('#cocheText').html('Cocher tout');// mise à jour du texte de cocheText
        }          
               
    });

});