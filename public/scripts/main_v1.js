$("#closmsgbox").click(function() {
    $("#msgbox").hide();
});

function showNewMessage(shorttext, longtext, cat) {
    //build new alert
    message = '<span class="message label ';
    message = message + 'label-' + cat + '" ';
    message = message + 'data-content="' + longtext + '">';
    message = message + shorttext;
    message = message + '</span>';
    $('#message-container').append(message);
    $('.message').popover({
        placement: 'bottom'
    });
    setTimeout(function() {
        $('.message').hide();
    }, 6000);
}
//Einsammeln der Nachrichten
// und Anzeigen in der Navbar
function getMessages(){
    $.get('/Apisystem/getmessages', function(data) {
        if ($.trim(data) != "") {
            showNewMessage(data['shorttext'], data['longtext'], data['cat']);
        }
    })
};

//Functions which should be run/available after Document.load
$(document).ready(function() {

    //Hole neue Nachrichten direkt nach dem Seitenaufbau
    getMessages();

    //Build Ajax-Icon function
    $("#ajaxIcon").ajaxStart(function() {
        $(this).show();
    });
    $("#ajaxIcon").ajaxStop(function() {
        $(this).hide();
    });

});