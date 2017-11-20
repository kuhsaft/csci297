function confirmForm() {
    var times = [];

    $(".time-slot.enabled > input:checkbox:checked").each(function(){
        times.push($(this).attr('id'));
    });

    console.log(times);
    if (times.length > 0) {
        var prompt = "Do you really want to remove these times?\n\n";
        times.forEach(function (t) {
            prompt += t + '\n';
        });

        return confirm(prompt);
    } else {
        return true;
    }
}
