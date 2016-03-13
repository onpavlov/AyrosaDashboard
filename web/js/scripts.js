$(function() {
    dashboard = {};
    dashboard.sort = [];

    $( "ul.dropable" ).sortable({
        items:"li:not(.ui-state-disabled)",
        connectWith: "ul",
        axis: "y",

        /* Сохраняем индексы сортировки перед перетаскиванием */
        start: function(event, ui) {
            if (dashboard.sort.length == 0) {
                $("ul.dropable").each(function(i, el) {
                    $(el).find("li > p").each(function(i, el) {
                        dashboard.sort.push($(el).data("sort"));
                    });
                });
            }
        },

        /* Проставляем приоритет и удаляем сообщение об отсутствии задач (если есть) */
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

        /* Назначаем новые индексы сортировки, приоритет и сохраняем данные */
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

    /* Выборка задач при изменении фильтра */
    $("select.implementer, select.project").change(function() {
        var filter = {
            "implementer" : $("select.implementer").val(),
            "project" : $("select.project").val()
        };

        $.ajax({
            url: "/task/ajax/",
            data: filter,
            dataType: "json",
            beforeSend: function() {
                $("div.preloader").fadeIn();
            },
            success: function(data) {
                renderList(data.high, "high", data.updatePriority);
                renderList(data.middle, "middle", data.updatePriority);
                renderList(data.low, "low", data.updatePriority);
                $("div.preloader").fadeOut();
            }
        });
    });

    function renderList(data, priority, canUpdate) {
        var ul = $("ul." + priority + "-priority");
        var li = "";

        $(data).each(function(i, el) {
            var user = (el.user) ? el.user : "Не назначена";

            li += "<li class='ui-sortable-handle'>";
            li += "<p class='task' data-sort='" + el.sort + "' data-priority='" + priority + "' data-id='" + el.id + "'>";
            li += (canUpdate) ? "<span class='glyphicon glyphicon-option-vertical' aria-hidden='true'></span>" : "<span aria-hidden='true'></span>";
            li += "<span class='title'>" + el.name + "<a target='_blank' href='" + el.task_url + "'><span class='glyphicon glyphicon-link' aria-hidden='true'></span></a></span>";
            li += "<span class='left'>";
            li += "<span class='user-info'>";
            li += "<span class='text-primary'>" + user + "</span>";
            li += "<span class='text-primary'>" + el.date + "</span>";
            li += "<span class='text-primary'><a target='_blank' href='" + el.project_url + "'>" + el.project + "</a></span>";
            li += "</span></span></p></li>";
        });

        if (!data.length) {
            li += "<p class='message ui-state-disabled' style='padding: 0 20px'>Задачи отсутствуют</p>";
        }

        ul.html(li);
    }
});