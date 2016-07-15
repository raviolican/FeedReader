$("document").ready(function () {
    var top = $('#sidebar').offset(),
     start = $(document).height(),
    page = 0,
    myPage = "",
    loading = false;
    $("#logout").click(function () {
        var data = "&type=logout";
        $.ajax({
            type: "POST",
            dataType: "",
            url: "http://localhost/FeedReader/users/performLougout",
            data: data,
            beforeSend: function () {},
            success: function (data) {
                if (data == "success") {
                    location.reload();
                }
                else{
                    alert(data);
                }
            }
        });
        return false;
    });
    $(".wan").on("click", ".btn", function () {
        var $this = $(this);
        $(this).parent().parent().find("#jdhsg").collapse("toggle");
    });
    $("#username").click(function () {
        $(".std").fadeOut("fast");
        url = "http://edrsvc.com/me.php";
        window.location = url;
    });
    $(".req").click(startPage);
    
    $(window).load(function () {
        loadHomePage(page, 3);
        page = 3;
    });
    $("#selectLanguage").on("change", function(){
       data = $(this).serialize();
       $.ajax({
           type: "get",
           dataType:"",
           url:"http://localhost/FeedReader/users/setLanguage",
           data: data,
           success: function(data){
               location.reload();
           }
       });
    });
    function loadHomePage(start, end) {
        myPage = "home";
        if (loading) {
            return;
        }
        if ($(".wan").length > 0) {
            $(".modal_load").fadeIn("slow");
            $("#home").closest("li").addClass("active").siblings().removeClass('active');;
            data = $(this).serialize() + "&" + $.param({
                "action": "Home"
                , "start": start
                , "end": end
            });
            loading = true;
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "inc/ajax.php",
                data: data,
                success: function (data) {
                    if (data == "") {
                        $(".modal_load").html("").fadeOut("slow").html("Alles geladen!").fadeIn("slow").fadeOut("slow");
                        exit();
                    }
                    $.each(data, function (i, item) {
                        $(".modal_load").html("").fadeOut("slow");
                        if (typeof item.desc[0] != "undefined") {
                            if (item.desc[0].length >= 160) {
                                $("#wan").append('<div class="card" style="max-height: 10;"><div class="card-block"><a href="' + data[i].link[0] + '"target="_blank"><h4 class="card-title"target="_blank">' + data[i].titlee[0] + '</h4></a><p class="card-text">' + data[i].desc[0] + '</p><p class="card-text"><small class="text-muted">' + data[i].pubDate + ' // ' + data[i].namee + '</small></p></div><div class="card-footer text-muted" ><b>' + data[i].tag + '</b></div></div>');
                            }
                            else {
                                $("#wan").append('<div class="card" style="max-height: 10;"><div class="card-block"><a href="' + data[i].link[0] + '"target="_blank"><h4 class="card-title"target="_blank">' + data[i].titlee[0] + '</h4></a><p class="card-text">' + data[i].desc[0] + '</p><p class="card-text"><small class="text-muted">' + data[i].pubDate + ' // ' + data[i].namee + '</small></p></div><div class="card-footer text-muted" ><b>' + data[i].tag + '</b></div></div>');
                            }
                        }
                        else {
                            $("#wan").append('<div class="card" style="max-height: 300;"><div class="card-block"><a href="' + data[i].link[0] + '"target="_blank"><h4 class="card-title">' + data[i].titlee[0] + '</h4></a><p class="card-text">Nicht Angegeben</p><p class="card-text"><small class="text-muted">' + data[i].pubDate + ' // ' + data[i].namee + '</small></p></div><div class="card-footer text-muted" ><b>' + data[i].tag + '</b></div></div>');
                        }
                        loading = false;
                        $(window).unbind('scroll', function () {});
                    });
                },
                error: function (xhr, textStatus, errorThrown) {
                }
            });
            return false;
        }
    }
    function startPage() {
         
        $(".modal_load").fadeIn("slow");
        var idName = $(this).attr('id');
        switch (idName) {
        case 'news':
            var data = {
                "action": "News"
            };
            myPage = "news";
            break;
        case 'sport':
            var data = {
                "action": "Sport"
            };
            myPage = "sport";
            break;
        case 'wirtschaft':
            var data = {
                "action": "Wirtschaft"
            };
            myPage = "wirtschaft";
            break;
        case 'geld':
            var data = {
                "action": "Geld"
            };
            break;
            myPage = "geld";
        case 'web':
            var data = {
                "action": "Web"
            };
            myPage = "web";
            break;
        case 'schlagzeilen':
            var data = {
                "action": "Schlagzeilen"
            };
            myPage = "schlagzeilen";
            break;
        case 'home':
            var data = {
                "action": "Home"
            };
            myPage = "home";
            break;
        default:
            var data = {
                "action": $(this).attr('id')
            };
                myPage = $(this).attr('id');
        }
        $(this).parent().addClass('active').siblings().removeClass('active');
        data = $(this).serialize() + "&" + $.param(data);
        $.ajax({
            type: "POST",
            dataType: "json", //json
            url: "inc/ajax.php",
            data: data,
            success: function (data) {
                $("#wan,.modal_load").html("").fadeOut("slow");
                $.each(data, function (i, item) {
                    if (typeof item.desc[0] != "undefined") {
                        if (item.desc[0].length >= 160) {
                            $("#wan").append('<div class="card"><div class="card-block"><a href="' + data[i].link[0] + '"target="_blank"><h4 class="card-title">' + data[i].titlee[0] + '</h4></a><p class="card-text">' + data[i].desc[0] + '</p><p class="card-text"><small class="text-muted">' + data[i].pubDate + ' // ' + data[i].namee + '</small></p></div><div class="card-footer text-muted" ><b>' + data[i].tag + '</b></div></div>').hide().fadeIn("slow");
                        }
                        else {
                            $("#wan").append('<div class="card"><div class="card-block"><a href="' + data[i].link[0] + '" target="_blank"><h4 class="card-title">' + data[i].titlee[0] + '</h4></a><p class="card-text">' + data[i].desc[0] + '</p><p class="card-text"><small class="text-muted">' + data[i].pubDate + ' // ' + data[i].namee + '</small></p></div><div class="card-footer text-muted" ><b>' + data[i].tag + '</b></div></div>').hide().fadeIn("slow");
                        }
                    }
                    else {
                        $("#wan").append('<div class="card"><div class="card-block"><a href="' + data[i].link[0] + '"target="_blank"><h4 class="card-title">' + data[i].titlee[0] + '</h4></a><p class="card-text">Nicht Angegeben</p><p class="card-text"><small class="text-muted">' + data[i].pubDate + ' // ' + data[i].namee + '</small></p></div><div class="card-footer text-muted" ><b>' + data[i].tag + '</b></div></div>').hide().fadeIn("slow");
                    }
                });
            }
        });
        return false;
    };
    $("#login-form").validate({
        rules: {
            loginInputEmail: {
                required: true,
                email: true
            },
            loginInputPWD: {
                required: true
            }
        },
        messages: {
            loginInputEmail: "E-Mail eingeben.",
            loginInputPWD: {
                required: "Passwort eingeben."
            }
        }
    });
    $("#loginBTN").click(function () {
        
        if ($("#login-form").valid()) {
            var data = $("#login-form").serialize();
            $.ajax({
                type: "GET",
                dataType: "",
                url: "userLogin",
                data: data,
                success: function (data) {
                    alert(data);
                        location.reload();
                }
            });
            return false;
        }
    });
    $("#register-form").validate({
        ignore: ".ignore",
        rules: {
            regInputEmail: {
                required: true,
                email: true
            },
            "hiddenRecaptcha": {
                required: function() {
                    if(grecaptcha.getResponse() == '') {
                        return true;
                    } else {
                        return false;
                    }
                }
            },
            regInputPWD: {
                required: true,
                minlength: 5,
                maxlength: 16
            },
            regInputPWD_re: {
                required: true,
                equalTo: '#regInputPWD'
            }
        },
        messages: {
            regInputEmail: 'Bitte geben Sie eine gültige Adresse ein.',
            regInputPWD: {
                required: "Bitte geben Sie ein Passwort ein.",
                minlegth: "Das Passwort ist zu kurz. Es sollte mindestens 5 Zeichen lang und maximal aus 16 Zeichen bestehen."
            },
            hiddenRecaptcha: {
                required: "Bitte lösen sie das Captcha!"
            },
            regInputPWD_re: {
                required: "Bitte wiederholen Sie das Passwort",
                equalTo: "Ihre eingegebenen Passwörter stimme nicht überein."
            }
        }
    });
    $("#registerBTN").click(function () {
        if ($("#register-form").valid()) {
            var data = $("#register-form").serialize();
            $.ajax({
                type: "GET",
                dataType: "",
                url: "registerNewUser",
                data: data,
                success: function (data) {
                    alert(data);
                    if (data == "1") {
                        alert("if");
                        $("#error").fadeIn(1000, function () {
                            $("#error").html('<div class="alert alert-danger">Sorry email already taken !</div>' + data);
                        });
                    }
                    else if (data == "success") {
                        alert("else if");
                        $(".modal-footer").html('<button type="button" class="btn btn-secondary" data-dismiss="modal">Schliessen</button>');
                        $(".modal-body").html("<p>Sie können sich nun einloggen!</p>");
                        $(".modal-title").html("Juhu!");
                    }
                    else {
                        alert("else");
                        grecaptcha.reset();
                        $("#error").fadeIn(1000, function () {
                            $("#error").html(data);
                        });
                    }
                }
            });
            return false;
        }
        else {}
    });
    $(window).scroll(function () {  
        /*
        if ($(window).scrollTop() == $(document).height() - ($(window).height())) {
                loadHomePage(page, 3);
                page += 3;
        }
        */
        // scroll down show sidebar hide topnav
         if($(window).scrollTop() >= $(window).height() - 500){

            $("#topnav").fadeOut("fast");
            $(".col-sm-12").removeClass("col-sm-12").addClass("col-sm-10");
            $(".col-sm-0").removeClass("col-sm-0").addClass("col-sm-2").css('visibility','visible').fadeIn("fast");
        }
        //reached top
         if ($(window).scrollTop() == 0) {
            $("#topnav").fadeIn("fast");
            $(".col-sm-2").removeClass("col-sm-2").addClass("col-sm-0").css('visibility','hidden').fadeOut("fast");
            $(".col-sm-10").removeClass("col-sm-10").addClass("col-sm-12");
        }
    });
});