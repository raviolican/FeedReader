$("document").ready(function () {
    $("#myselect").change(function () {
        var data = $("#mySelection").serialize() + "&type=userSelect";
        $.ajax({
            type: "POST",
            dataType: "",
            url: "inc/ajax.php",
            data: data,
            beforeSend: function () {},
            success: function (data) {
                if (data == "success") {
                    $("#lol").html(data);
                }
                $("#usinfo").html("Gespeichert").fadeIn("fast").delay(100).fadeOut("fast");
            }
        });
        return false;
    });
    $("#addFeed-form").validate({
        rules: {
            feedName: {
                required: true
            },
            feedUrl: {
                required: true
            },
            f_category: {
                required: true
            }
        },
        messages: {
            feedName: {
                required: "Bitte Name eingeben"
            },
            feedUrl: {
                required: "Feed URL eingeben"
            },
            f_category: {
                required: "Bitte Kategorie auswählen"
            }
        }
    });
    $("#sendFeed").click(function () {
        if ($("#addFeed-form").valid()) {
            $("#error").html("Überprüfen...");
            var data = $("#addFeed-form").serialize() + "&type=addFeed";
            $.ajax({
                type: "POST",
                dataType: "",
                url: "inc/ajax.php",
                data: data,
                success: function (data) {
                    if(data == "added"){
                        $("#error").html("Hinzugefügt");  
                    }
                    else{
                        $("#error").html("Fehler:<br>"+data);
                    }
                }
            });
        }
    });
    
    $("#addCategory-form").validate({
        rules: {
            categoryName: {
                required : true
            },
            messages : {
                categoryName: {
                    required : "Bitte Name der Kategorie eingeben"
                }
            }
        }
    });
    $("#sendCategory").click(function () {
        if($("#addCategory-form").valid()){
            $("#error_cat").html("Überprüfen...");
            var data = $("#addCategory-form").serialize() + "&type=addCategory";
            $.ajax({
                type: "POST",
                dataType: "",
                url: "inc/ajax.php",
                data: data,
                success: function (data) {
                    $("#error_cat").html(data);
                }
            });
        }
    });
    $(".del").click(function(){
        var data = $.param({
            "type" : "delUserFeed",
            "key" : $(this).parent().attr("value")
        });
        $(this).parent().remove();
        $.ajax({
            type: "POST",
            dataType: "",
            url: "inc/ajax.php",
            data: data,
            success: function (data) {
                $("#usinfo").html(data).fadeIn("fast").fadeOut("slow");
            }
        });
        
    });
});
