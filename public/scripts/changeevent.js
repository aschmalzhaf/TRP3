$(document).ready(

    function () {
        // general vars
        var selectedshift = $("#selectedshift").val();
        var standarddate = $("#standarddate").val();

        // initial load
        function loadshifts() {
            $.ajax({
                type: "POST",
                url: "/Eventshift/getshifts",
                data: {
                    ID: $("#form_ID").val()
                },
                success: function (data) {
                    $("#shiftslist").html(data);
                    if (selectedshift != "") {
                        $("#shiftslist option[value=" + selectedshift + "]").attr("selected", "selected");
                        $("#shiftslist option[value=" + selectedshift + "]").click();
                        selectedshift = 0;
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert("Fehler! " + textStatus);
                }

            });
        } ;

        loadshifts();

        // clear form


        function clearForm(form) {
            // iterate over all of the inputs for the form
            // element that was passed in
            $(':input', form).each(

                function () {
                    var type = this.type;
                    var tag = this.tagName.toLowerCase(); // normalize
                    // case
                    // it's ok to reset the value attr of text
                    // inputs,
                    // password inputs, and textareas
                    if (type == 'text' || type == 'password' || type == 'hidden' || tag == 'textarea') this.value = "";
                    // checkboxes and radios need to have their
                    // checked state
                    // cleared
                    // but should *not* have their 'value'
                    // changed
                    else if (type == 'checkbox' || type == 'radio') this.checked = false;
                // select elements need to have their
                // 'selectedIndex' property
                // set to -1
                // (this works for both single and multiple
                // select elements)
                });

        }

        ;

        // validation for shift
        $.validator.addMethod("customtime", function (value, element) {
            if (value.length == 0) {
                return true;
            }
            var parts = value.split(":");
            // check length of parts
            if (parts.length == 2) {
            // later
            } else {
                var parts = value.split(".");
                if (parts.length == 2) {
                // later
                } else {
                    var parts = value.split(",");
                    if (parts.length == 2) {
                    // later
                    } else {
                        return false;
                    }
                }
            }

            if (parts[0].length > 2) {
                return false;
            }
            ;

            if (parts[1].length > 2) {
                return false;
            }
            ;
            return true;

        }, "Fehler!!!");

        $("#shiftform").validate({
            rules: {
                form_shiftendtime: {
                    customtime: true,
                    required: true
                },
                form_shiftstarttime: {
                    customtime: true,
                    required: true
                }
            }
        });

        // save data and load table
        $("#saveevent").click(

            function () {
                // Save Data
                $.ajax({
                    type: "POST",
                    url: "/Eventshift/changeevent",
                    data: {
                        action: "saveevent",
                        ID: $('#form_ID').attr('value'),
                        name: $('#form_name').attr('value'),
                        locationid: $('#form_locationid').attr('value'),
                        leadid: $('#form_leadid').attr('value'),
                        note: $('#form_eventnote').val(),
                        horelevant: $('#horelevant').is(':checked')
                    },
                    error: function (data) {
                        alert(data);
                    },
                    success: function (data) {
                        $("#flashmsgcont").html(data);
                        $("#flashmsg").show();
                    }
                });
                return false;
            });

        // Fill Fields of shift form
        $("#shiftslist").click(

            function () {
                if ($(this).val() != -1) {
                    // load shift data
                    $.ajax({
                        type: "POST",
                        url: "/Eventshift/getshiftdata",
                        data: "ID=" + $("#shiftslist option:selected").val(),
                        success: function (data) {
                            var shift = JSON.parse(data);
                            $("#form_shiftID").val(
                                shift.ID);
                            $("#form_shiftname").val(
                                shift.name);
                            $("#form_shiftstartdate").val(
                                shift.startdate);
                            $("#form_shiftstarttime").val(
                                shift.starttime);
                            $("#form_shiftenddate").val(
                                shift.enddate);
                            $("#form_shiftendtime").val(
                                shift.endtime);
                            $("#form_shiftreqstaff").val(
                                shift.reqstaff);
                            $("#form_shiftnote").val(
                                shift.note);
                            // load added user
                            // data
                            loadaddedusers();

                            // load possible
                            // user data
                            loadposusers();
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            alert("Fehler! " + textStatus);
                        }

                    });

                } else {
                    $("#form_shiftID").val("");
                    $("#form_shiftname").val("");
                    $("#form_shiftstartdate").val("");
                    $("#form_shiftstarttime").val("");
                    $("#form_shiftenddate").val("");
                    $("#form_shiftendtime").val("");
                    $("#form_shiftreqstaff").val("");
                    $("#form_shiftnote").val("");
                    $("#addedusers").html("");
                    $("#possibleusers").html("");
                    $("#possibleusersclone").html("");

                }
                $("#assignfilter").val("");
            });

        // load added users


        function loadaddedusers() {
            $.ajax({
                type: "POST",
                url: "/Eventshift/getusers",
                data: "ID=" + $("#form_shiftID").val() + "&selection=added",
                success: function (data) {
                    $("#addedusers").html(data);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert("Fehler! " + textStatus);
                }
            });
        }

        ;

        // load possible users


        function loadposusers() {
            var action = $(".seluserselection:checked:first").val();
            $.ajax({
                type: "POST",
                url: "/Eventshift/getusers",
                data: "ID=" + $("#form_shiftID").val() + "&selection=" + action + "&remfull=" +$("#remfull:checked").val(),
                success: function (data) {
                    $("#possibleusers").html(data);
                    $("#possibleusersclone").html(data);
                    // use filter
                    $("#assignfilter").keyup();
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert("Fehler! " + textStatus);
                }
            });
        }

        ;

        $("#remfull").change(function () {
            loadposusers();
        });

        // save or update shift!
        $("#saveshift").click(

            function () {

                // check users
                $("#addedusers option").filter(

                    function (index) {
                        var username = $(
                            this).text();
                        var userid = $(this).val();
                        var remove = false;
                        $.ajax({
                            type: "POST",
                            url: "/Eventshift/checkshiftnewtime",
                            async: false,
                            data: {
                                userID: $(
                                    this).val(),
                                shiftID: $("#form_shiftID").val(),
                                startdate: $("#form_shiftstartdate").val(),
                                starttime: $("#form_shiftstarttime").val(),
                                enddate: $("#form_shiftenddate").val(),
                                endtime: $("#form_shiftendtime").val()
                            },
                            error: function (data) {
                                alert(data);
                            },
                            success: function (data) {
                                if (data == "false") {
                                    if (confirm(username + ": \u00DCberschneidung l\u00f6schen?")) {
                                        $.ajax({
                                            type: "POST",
                                            async: false,
                                            url: "/Eventshift/changelink",
                                            data: "shiftid=" + $("#form_shiftID").val() + "&userid=" + userid + "&mode=delete",
                                            success: function (data) {
                                            },
                                            error: function (XMLHttpRequest, textStatus, errorThrown) {
                                                alert("Fehler! " + textStatus);
                                            }
                                        });

                                        remove = true;
                                    }
                                }

                            }
                        });
                        loadaddedusers();
                        loadposusers();
                        return remove;
                    }).remove();

                // save shift
                $.ajax({
                    type: "POST",
                    url: "/Eventshift/saveshift",
                    data: {
                        action: "saveevent",
                        ID: $("#form_shiftID").val(),
                        name: $("#form_shiftname").val(),
                        eventid: $("#form_ID").val(),
                        startdate: $("#form_shiftstartdate").val(),
                        starttime: $("#form_shiftstarttime").val(),
                        enddate: $("#form_shiftenddate").val(),
                        endtime: $("#form_shiftendtime").val(),
                        reqstaff: $("#form_shiftreqstaff").val(),
                        note: $("#form_shiftnote").val()
                    },
                    error: function (data) {
                        alert(data);
                    },
                    success: function (data) {
                        var res = JSON.parse(data);
                        // show message
                        $("#flashmsgcont").html(res.text);
                        $("#flashmsg").show();
                        $("#form_shiftID").val(
                            res.ID);
                        loadaddedusers();
                        loadposusers();
                        // select entry in shift
                        // list
                        $("#shiftslist option [value=" + res.ID + "]").click();
                        $.ajax({
                            type: "POST",
                            url: "/Eventshift/getshifts",
                            data: {
                                ID:$("#form_ID").val()
                            },
                            success: function (data) {
                                $("#shiftslist").html(
                                    data);
                            },
                            error: function (XMLHttpRequest, textStatus, errorThrown) {
                                alert("Fehler! " + textStatus);
                            }

                        });
                    }
                });
                return false;
            });

        // delete event
        // solved via standard form round up
        // delete shift
        $("#delshift").click(

            function () {
                if ($("#form_shiftID").val() != "") {
                    if (confirm('Schicht wirklich löschen?')) {
                        $.ajax({
                            type: "POST",
                            url: "/Eventshift/delshift",
                            data: "ID=" + $("#form_shiftID").val(),
                            success: function (data) {

                                $("#flashmsgcont").html(data);
                                $("#flashmsg").show();
                            },
                            error: function (XMLHttpRequest, textStatus, errorThrown) {
                                alert("Fehler! " + textStatus);
                            }

                        });

                        $("#form_shiftID").val("");
                        $("#form_shiftname").val("");
                        $("#form_shiftstartdate").val("");
                        $("#form_shiftstarttime").val("");
                        $("#form_shiftenddate").val("");
                        $("#form_shiftendtime").val("");
                        $("#form_shiftreqstaff").val("");
                        $("#form_shiftnote").val("");
                        $("#addedusers").html("");
                        $("#possibleusers").html("");
                        $("#possibleusersclone").html("");
                        $("#assignfilter").val("");

                        loadshifts();

                    }
                }
                ;
                return false;
            });

        // Assign Staff
        $("#assignuser").click(

            function () {
                if ($("#form_shiftID") != "" && $("#possibleusers option:selected").size() != 0) {
                    // check if there are allready
                    // enough MAs or no max is
                    // given
                    if ($("#addedusers option").size() >= $("#form_shiftreqstaff").val() && $("#form_shiftreqstaff").val() != "") {
                        alert("Es sind bereits ausreichend MAs eingebucht!");
                    } else {
                        $.ajax({
                            type: "POST",
                            url: "/Eventshift/changelink",
                            data: "shiftid=" + $("#form_shiftID").val() + "&userid=" + $("#possibleusers option:selected").val() + "&mode=insert",
                            success: function (data) {
                                if (data == "error") {
                                    if (confirm("Zeit\u00fCberschneidung - trotzdem eingetragen?")) {
                                        $.ajax({
                                            type: "POST",
                                            url: "/Eventshift/changelink",
                                            data: "shiftid=" + $("#form_shiftID").val() + "&userid=" + $("#possibleusers option:selected").val() + "&mode=insertanyway",
                                            success: function (data) {
                                                loadaddedusers();
                                                loadposusers();
                                            },
                                            error: function (XMLHttpRequest, textStatus, errorThrown) {
                                                alert("Fehler! " + textStatus);
                                            }
                                        });
                                    }
                                }
                                loadaddedusers();
                                loadposusers();

                            },
                            error: function (XMLHttpRequest, textStatus, errorThrown) {
                                alert("Fehler! " + textStatus);
                            }
                        });
                    }
                }
                ;

                return false;
            });

        // Delete Assignment
        $("#delassignment").click(

            function () {
                if ($("#form_shiftID") != "" && $("#addedusers option:selected").size() != 0) {

                    $.ajax({
                        type: "POST",
                        url: "/Eventshift/changelink",
                        data: "shiftid=" + $("#form_shiftID").val() + "&userid=" + $("#addedusers option:selected").val() + "&mode=delete",
                        success: function (data) {
                            loadposusers();
                            loadaddedusers();
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            alert("Fehler! " + textStatus);
                        }
                    });
                }
                ;
                return false;
            });

        // HELPER
        // Helper: copy date to end field
        $("#form_shiftenddate").focus(function () {
            if ($(this).val() == "") $(this).val($("#form_shiftstartdate").val());
        });

        // Helper: change date with time
        $("#form_shiftstartdate").focus(function () {
            $("#form_shiftstarttime").focus();
        });
        $("#form_shiftstartdate").dblclick(function () {
            $("#form_shiftstartdate").focus();
        });

        $("#form_shiftenddate").focus(function () {
            $("#form_shiftendtime").focus();
        });
        $("#form_shiftenddate").dblclick(function () {
            $("#form_shiftenddate").focus();
        });

        // change date
        $("#form_shiftendtime").blur(

            function () {
                var parts = $("#form_shiftendtime").val().split(":");
                if (parts[0] < 17) {
                    var parts = standarddate.split(".");
                    $("#form_shiftenddate").val((parseInt(parts[0]) + 1).toString() + "." + parts[1].toString() + "." + parts[2].toString());
                } else {
                    var parts = standarddate.split(".");
                    $("#form_shiftenddate").val((parts[0]).toString() + "." + parts[1].toString() + "." + parts[2].toString());
                }
            });


        // Helper: time fields
        $("#form_shiftstarttime").blur(function () {
            var parts = $(this).val().split(":");
            // check length of parts
            if (parts.length == 2) {
            // later
            } else {
                var parts = $(this).val().split(".");
                if (parts.length == 2) {
                // later
                } else {
                    var parts = $(this).val().split(",");
                    if (parts.length == 2) {
                    // later
                    } else {
                        return;
                    }
                }
            }
            if (parts[0].length == 1) {
                parts[0] = "0" + parts[0];
            }
            ;
            if (parts[1].length == 1) {
                parts[1] = parts[1] + "0";
            }
            ;
            $(this).val(parts[0] + ":" + parts[1]);
        });

        $("#form_shiftstarttime").blur(
            function () {
                var parts = $("#form_shiftstarttime").val().split(":");
                if (parts[0] < 17) {
                    var parts = standarddate.split(".");
                    $("#form_shiftstartdate").val((parseInt(parts[0]) + 1).toString() + "." + parts[1].toString() + "." + parts[2].toString());
                } else {
                    var parts = standarddate.split(".");
                    $("#form_shiftstartdate").val(parts[0].toString() + "." + parts[1].toString() + "." + parts[2].toString());
                }
            });


        $("#form_shiftendtime").blur(function () {
            var parts = $(this).val().split(":");
            // check length of parts
            if (parts.length == 2) {
            // later
            } else {
                var parts = $(this).val().split(".");
                if (parts.length == 2) {
                // later
                } else {
                    var parts = $(this).val().split(",");
                    if (parts.length == 2) {
                    // later
                    } else {
                        return;
                    }
                }
            }
            if (parts[0].length == 1) {
                parts[0] = "0" + parts[0];
            }
            ;
            if (parts[1].length == 1) {
                parts[1] = parts[1] + "0";
            }
            ;
            $(this).val(parts[0] + ":" + parts[1]);
        });


        // Helper: open data of User on double cklick
        $(".userlist").live("dblclick", function () {
            window.open("/Captteam/staffdatacheck/" + $(this).val(), "_blank");
        });

        // load possible users list
        $(".seluserselection").click(function () {
            if ($("#form_shiftID").val() != "") loadposusers();
        });

        // special location panel
        $("#locationpanelbutton").click(function () {
            $("#locationpanel").css("display", "");
            $("#overlay").css("display", "");

        });

        $(".locationvaluebutton").click(function (data) {
            $("#form_locationid").val($(this).attr("id"));
            if ($(this).attr("id") != "") {
                $("#form_location").val($(this).text());
            } else {
                $("#form_location").val("");
            }

            $("#locationpanel").css("display", "none");
            $("#overlay").css("display", "none");
            return false;
        });

        // special lead panel
        $("#leadpanelbutton").click(function () {
            $("#leadpanel").css("display", "");
            $("#overlay").css("display", "");

        });

        $(".leadvaluebutton").click(function (data) {
            $("#form_leadid").val($(this).attr("id"));
            if ($(this).attr("id") != "") {
                $("#form_lead").val($(this).text());
            } else {
                $("#form_lead").val("");
            }

            $("#leadpanel").css("display", "none");
            $("#overlay").css("display", "none");
            return false;
        });

        // build assign filter
        $("#assignfilter").keyup(

            function () {
                $("#possibleusers option").remove();
                $("#possibleusers").append(
                    $("#possibleusersclone option").clone());
                if ($("#assignfilter").val() != "") {
                    $("#possibleusers option").filter(

                        function (index) {

                            if ($(this).text().toLowerCase().indexOf(
                                $("#assignfilter").val().toLowerCase()) == -1) {
                                return true;
                            } else {
                                return false;
                            }
                        ;
                        }).remove();
                }
            ;
            })
        //fast jump panel Funktionen...
        //Einblenden und Ausblenden
        $("#toggler").click(function() {
            $("#fj").toggle("slide", {
                direction: "right"
            }, 500, function() {
                if ($("#fj").html() == "") {
                    $.ajax({
                        url: '/Apieventshift/Getfastjumpevents',
                        success: function(data, textStatus) {
                            //alert(data[0].name);
                            $.each(data, function(index, value) {
                                $("#fj").append("<a href='/Eventshift/changeevent/" + value.ID + "'>" + value.name + "</a><br>");
                            })
                        }
                    });
                }
                return false;
            });

        });


    });