$(document).ready(function() {
    $("button.parse_data").click(function() {
        var data = [];

        $("input[type=checkbox]:checked").each(function(i, el) {
            var value = $(el).val();

            if (value == "projects") {
                parse({"action" : value + "Update"});
                var projects = getProjects();

                $(projects).each(function(i, el) {
                    data.push({"action" : "taskUpdate", "params" : el});
                });
            } else {
                data.push({"action" : value + "Update"});
            }
        });

        if (data.length) {
            var step = 100 / data.length;
            var progress = $(".progress-bar");
            $(".progress").removeAttr("style");
            progress.addClass("active");

            $(data).each(function(i, el) {
                parse(el);
                var current = step * (i + 1);
                progress.html(~~current + "%");
                progress.data("aria-valuenow", ~~current);
                progress.width(~~current + "%");
            });

            progress.removeClass("active");
        }
    });

    function parse(item) {
        $.ajax({
            url: "tools/update",
            dataType: "json",
            data: item,
            async: false,
            success: function(data) {
                var result = "";

                $(data).each(function(i, el) {
                    if (el.status == "success") {
                        result += "<p class='text-success'>" + el.message + "</p>"
                    } else {
                        result += "<p class='text-danger'>" + el.message + "</p>"
                    }
                });
                $("#result-bc").append(result);
            },
            error: function() {
                console.log("error");
            }
        });
    }

    function getProjects() {
        var result;

        $.ajax({
            url: "tools/projects",
            async: false,
            dataType: "json",
            success: function(data) {
                result = data;
            }
        });

        return result;
    }
});