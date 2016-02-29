$(function() {
    dashboard = {};
    dashboard.sort = [];

    $( "ul.dropable" ).sortable({
        items:"li:not(.ui-state-disabled)",
        connectWith: "ul",
        axis: "y",

        start: function(event, ui) {
            if (dashboard.sort.length == 0) {
                $("ul.dropable").each(function(i, el) {
                    $(el).find("li > p").each(function(i, el) {
                        dashboard.sort.push($(el).data("sort"));
                    });
                });
            }
        },

        beforeStop: function(event, ui) {
            var parent = ui.item.parent();

            if (parent.hasClass("high-priority")) {
                ui.item.find("p.task").attr("data-priority", "high");
            } else if (parent.hasClass("middle-priority")) {
                ui.item.find("p.task").attr("data-priority", "middle");
            } else if (parent.hasClass("low-priority")) {
                ui.item.find("p.task").attr("data-priority", "low");
            }

            if (parent.find("li").length && parent.find("p.message").length) {
                parent.find("p.message").remove();
            }
        },

        stop: function() {
            dashboard.i = 0;
            dashboard.data = {"id":[], "sort":{}, "priority":[]};

            $("ul.dropable").each(function(i, el) {

                dashboard.temp = [];

                /* Проставляем индексы сортировки */
                $(el).find("p.task").each(function(i, el) {
                    $(el).attr("data-sort", dashboard.sort[dashboard.i]);

                    dashboard.data.id.push($(el).attr("data-id"));
                    dashboard.temp.push($(el).attr("data-sort"));

                    dashboard.i++;
                });

                if ($(el).hasClass("high-priority")) {
                    dashboard.data.sort.high = dashboard.temp;
                } else if ($(el).hasClass("middle-priority")) {
                    dashboard.data.sort.middle = dashboard.temp;
                } else if ($(el).hasClass("low-priority")) {
                    dashboard.data.sort.low = dashboard.temp;
                }

                if (!$(el).find("li").length)
                    $(el).html("<p class='message ui-state-disabled' style='padding: 0 20px'>Задачи отсутствуют</p>");
            });

            $.post(
                "/task/update/",
                dashboard.data
            );
        }
    }).disableSelection();

    //$( "#sortable1, #sortable2, #sortable3" ).disableSelection();
});