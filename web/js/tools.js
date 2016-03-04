$(document).ready(function() {
    $("button.parse_users").click(function() {
        parse("users");
    });

    function parse(item) {
        $.ajax({
            url: "tools/parse",
            dataType: "json",
            data: {"item" : item},
            success: function(data) {
                var result = "";

                $(data).each(function(i, el) {
                    if (el.status == "success") {
                        result += "<p class='text-success'>" + el.message + "</p>"
                    } else {
                        result += "<p class='text-danger'>" + el.message + "</p>"
                    }
                });
                $("#result-bc").html(result);
            },
            error: function() {
                console.log("error");
            }
        });
    }
});