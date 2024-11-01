function make_trie(inflex, lang) {

    if (inflex) {
        jQuery.getJSON(init.url1 + lang + "&callback=?", function (data) {

                if (undefined === data || undefined === data.status) {

                    alert("Error making trie");

                } else {
                    jQuery("#stamp" + lang + "i").html(data.inflexions[lang]);
                    new Effect.Highlight(document.getElementById("stamp" + lang + "i"), {
                        startcolor: '#25FF00',
                        endcolor: '#FFFFCF'
                    });
                }

            }
        );
    } else {
        jQuery.getJSON(init.url2 + lang + "&callback=?", function (data) {

                if (undefined === data || undefined === data.status) {

                    alert("Error making trie");

                } else {
                    jQuery("table#trie_table").find("#stamp" + lang).html(data.terms[lang]);
                    new Effect.Highlight(document.getElementById("stamp" + lang), {
                        startcolor: '#25FF00',
                        endcolor: '#FFFFCF'
                    });
                }
            }
        )
    }
}
