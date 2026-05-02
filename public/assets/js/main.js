$(document).ready(function () {
    // Placeholder rotation
    const placeholders = [
        "https://www.tiktok.com/@username/video/123456...",
        "Dán liên kết video TikTok vào đây..."
    ];
    let pIndex = 0;
    
    window.setInterval(function () {
        pIndex = (pIndex + 1) % placeholders.length;
        $("input[name='tiktok-url']").attr("placeholder", placeholders[pIndex]);
    }, 3000);

    // Form submit animation
    $("#downloadForm").on('submit', function() {
        var btn = $("#submitBtn");
        btn.css("pointer-events", "none");
        btn.css("opacity", "0.8");
        $("#btnText").text("Đang xử lý...");
        $("#defaultIcon").hide();
        $("#spinner").show();
    });
});
