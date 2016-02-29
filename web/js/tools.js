$(document).ready(function() {
    $("button.parse").click(function() {
        $.ajax({
            url: "tools/parse",
            dataType: "xml",
            success: function(data) {console.log(data);
                var peoples = $(data).find("people > person");
                var out = "";

                $(peoples).each(function(i, el) {
                    var fitrstname = $(el).find("first-name").text();
                    var lastname = $(el).find("last-name").text();
                    out += fitrstname + " " + lastname + "<br>";
                });

                $("#result-bc").html(out);
            }
        });
    });
});