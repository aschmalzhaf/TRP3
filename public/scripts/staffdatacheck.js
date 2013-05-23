/**
 * User: 001
 * Date: 21.06.11
 * Time: 02:07
 */
$(document).ready(function () {
    //vars
    var userid = $("#ID").text();
    //validation...
    $.validator.addMethod("customdate", function(value, element) {
        if (value.length == 0) {
            return true;
        }

        var dateparts = value.split(".");
        //check length of parts
        if (dateparts[0].length != 2
                || dateparts[1].length != 2
                || dateparts[2].length != 4
                || dateparts.length > 3) {
            return false;
        }
        //check value ranges
        if (dateparts[0] < 1 || dateparts[0] > 31
                || dateparts[1] < 1 || dateparts[1] > 12
                || dateparts[2] < 0) {
            return false;
        }
        //else
        return true;
    }, "Datum nicht korrekt!");

    $("#corrform").validate({
        rules: {
            fname: {
                required: true },
            lname: {
                required: true },
            birthday: {
                required: false,
                customdate: true },
            mphone: {
                required: false },
            city: {
                required: false },
            kv: {
                required: false },
            intnote: {
                required: false },
            email: {
                required: false,
                email: true        }
        },
        messages: {
            fname: {
                required: "Name notwendig!" },
            lname: {
                required: "Name notwendig!" },
            birthday: {
                required: "Geburtstag notwendig!"},
            email: {
                required: "eMail-Adresse notwendig!",
                email: "eMail-Adresse ung&uuml;ltig!" }
        }

    });


    //load added shifts
    function loadaddedshifts() {
        $.ajax({
            type: "POST",
            url: "/Captteam/getshifts",
            data: "ID=" + userid
                    + "&mode=added",
            success: function(data) {
                $("#addedshifts").html(data);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert("Fehler! " + textStatus);
            }
        });
    }

    ;

    loadaddedshifts();

    //load possible shifts
    function loadposshifts() {
        var mode = $(".shiftselection:checked:first").val();
        $.ajax({
            type: "POST",
            url: "/Captteam/getposshifts",
            data: "ID=" + userid
                    + "&mode=" + mode
                    + "&clearfull=" + $("#clearfull:checked").val(),
            success: function(data) {
                $("#possibleshifts").html(data);
                $("#possibleshiftsclone").html(data);
                //use filter
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert("Fehler! " + textStatus);
            }
        });
    }

    ;

    //initial load
    loadposshifts();

    $(".shiftselection").click(function() {
        loadposshifts();
    });


    //build filter
    $("#filter").keyup(function() {
        $("#possibleshifts option").remove();
        $("#possibleshifts").append($("#possibleshiftsclone option").clone());
        if ($("#filter").val() != "") {
            $("#possibleshifts option").filter(
                    function(index) {

                        if ($(this).text().toLowerCase().indexOf($("#filter").val().toLowerCase()) == -1) {
                            return true;
                        } else {
                            return false;
                        }
                        ;
                    }).remove();
        }
        ;
    });


    //Assign Shift
    $("#assign").click(function() {
        if (userid != "" && $("#possibleshifts option:selected").size() != 0) {
            //check if there are allready enough MAs or no max is given

            var shiftid = $("#possibleshifts option:selected").val().split("/")[1];
            $.ajax({
                type: "POST",
                url: "/Eventshift/changelink",
                data: "shiftid=" + shiftid
                        + "&userid=" + userid
                        + "&mode=insert",
                success: function(data) {
                    if (data == "error") {
                        if (confirm("Zeit\u00fCberschneidung - trotzdem eingetragen?")) {
                            $.ajax({
                                type: "POST",
                                url: "/Eventshift/changelink",
                                data: "shiftid=" + shiftid
                                        + "&userid=" + userid
                                        + "&mode=insertanyway",
                                success: function(data) {
                                    loadaddedshifts();
                                    loadposshifts();
                                },
                                error: function(XMLHttpRequest, textStatus, errorThrown) {
                                    alert("Fehler! " + textStatus);
                                }
                            });
                        }
                    }
                    loadposshifts();
                    loadaddedshifts();
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert("Fehler! " + textStatus);
                }
            });
        }
        ;

        return false;
    });


    //Delete Assignment
    $("#delassignment").click(function() {
        if (userid != "" && $("#addedshifts option:selected").size() != 0) {
            var shiftid = $("#addedshifts option:selected").val().split("/")[1];

            $.ajax({
                type: "POST",
                url: "/Eventshift/changelink",
                data: "shiftid=" + shiftid
                        + "&userid=" + userid
                        + "&mode=delete",
                success: function(data) {
                    loadposshifts();
                    loadaddedshifts();
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert("Fehler! " + textStatus);
                }
            });
        }
        ;
        return false;
    });


    //special job panel
    $("#sjobpanelbutton").click(function() {
        $("#sjobpanel").css("display", "");
        $("#overlay").css("display", "");

    });

    $(".sjobvaluebutton").click(function(data) {
        $("#form_sjobid").val($(this).attr("id"));
        if ($(this).attr("id") != "") {
            $("#form_sjob").val($(this).text());
        } else {
            $("#form_sjob").val("");
        }

        $("#sjobpanel").css("display", "none");
        $("#overlay").css("display", "none");

    });

    //HELPER
    //Open reference by doubleclick
    $(".shiftoptions").live("dblclick", function() {
        window.open("/Eventshift/changeevent/" + $(this).val(), "_blank");
    });

    $("#del_button").click(function() {
        $('input[name="informviamail"][type="hidden"]').val($('input[name="informviamail"][type="checkbox"]').attr("checked"));
        return confirm('Mitarbeiter wirklich löschen?');
    });

    //ready to start...
});
