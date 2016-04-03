$(document).ready(function() {
    updOrder = [];
    progress = {};
    progress.i = 0;

    $("button.parse_data").click(function() {
        $("#result-bc").html("");

        $("input[type=checkbox]:checked").each(function(i, el) {
            var value = $(el).val();

            if (value == "projects") {
                getProjects();
            } else {
                updOrder.push({"action" : value + "Update"});
            }
        });

        if (updOrder.length) {
            progress.step = 100 / updOrder.length;
            progress.bar = $(".progress-bar");
            $(".progress").removeAttr("style");
            progress.bar.addClass("active");
        }

        parse();
    });

    function parse() {
        $.ajax({
            url: "tools/update",
            dataType: "json",
            data: updOrder[progress.i],
            success: function(data) {
                progress.current = progress.step * (progress.i + 1);
                progress.bar.html(~~progress.current + "%");
                progress.bar.data("aria-valuenow", ~~progress.current);
                progress.bar.width(~~progress.current + "%");

                if (progress.current == 100) {
                    progress.bar.removeClass("active");
                }

                var result = "";
                $(data).each(function(i, el) {
                    if (el.status == "success") {
                        result += "<p class='text-success'>" + el.message + "</p>"
                    } else {
                        result += "<p class='text-danger'>" + el.message + "</p>"
                    }
                });
                $("#result-bc").append(result);
                progress.i += 1;
            },
            error: function(obj, error) {
                progress.current = progress.step * (progress.i + 1);
                progress.bar.html(~~progress.current + "%");
                progress.bar.data("aria-valuenow", ~~progress.current);
                progress.bar.width(~~progress.current + "%");

                if (progress.current == 100) {
                    progress.bar.removeClass("active");
                }
                console.log(error);
                progress.i += 1;
            },
            complete: function () {
                if (progress.current < 100) {
                    parse();
                }
            }
        });
    }

    function getProjects() {
        $.ajax({
            url: "tools/projects",
            dataType: "json",
            async: false,
            success: function(data) {
                $(data).each(function (i, el) {
                    updOrder.push({"action" : "taskUpdate", "params" : el});
                });
            }
        });
    }
});
