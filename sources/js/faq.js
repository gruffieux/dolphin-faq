function feedback(id, vote) {
    $.ajax({
        type: "GET",
        url: "modules",
        data: "r=faq/feedback/"+id+"/"+vote,
        dataType: "text",
        success: function(data) {
            alert(data);
        }
    });
}