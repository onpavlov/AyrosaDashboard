$(document).ready(function() {
    $("button.parse_data").click(function() {
        var len     = 0;
        progress    = {};
        progress.i  = 0;
        progress.data = [];

        $("#result-bc").html("");

        $("input[type=checkbox]:checked").each(function(i, el) {
            var value = $(el).val();

            /* Формируем объект с данными для обновления */
            if (value == "projects") {
                updateProjects();
                len = 1;
            } else {
                progress.data.push({"action" : value + "Update"});
            }
        });

        if (progress.data.length) {
            progress.step   = 100 / (progress.data.length + len);
            progress.bar    = $(".progress-bar");
            $(".progress").removeAttr("style");
            progress.bar.addClass("active");

            $(progress.data).each(function(i, el) {
                parse(el);
            });
        }
    });

    function parse(item, async) {
        $.ajax({
            url: "tools/update",
            dataType: "json",
            data: item,
            async: async || true,
            success: function(data) {
                /* Обновление прогресс-бара */
                progress.current = progress.step * (progress.i + 1);
                progress.bar.html(~~progress.current + "%");
                progress.bar.data("aria-valuenow", ~~progress.current);
                progress.bar.width(~~progress.current + "%");
                progress.i += 1;

                if (~~progress.current == 100) {
                    progress.bar.removeClass("active");
                }

                /* Вывод сообщений в лог результатов */
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

    /* Запрашивает список  */
    function updateProjects() {
        parse({"action" : "projectsUpdate"}, false);

        $.ajax({
            url: "tools/projects",
            async: false,
            dataType: "json",
            success: function(data) {
                $(data).each(function(i, el) {
                    progress.data.push({"action" : "taskUpdate", "params" : el});
                });
            }
        });
    }
});