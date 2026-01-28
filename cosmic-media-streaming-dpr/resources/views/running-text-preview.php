<style>
    .marquee {
        width: 100%;
        overflow: hidden;
        border:1px solid #ccc;
        background: red;
        color: white;
        font-weight:bold;
    }
</style>
<div class='marquee'>LONGER TEXT LOREM IPSUM DOLOR SIT AMET, CONSECTETUR ADIPISCING ELIT END.</div> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.marquee/1.3.1/jquery.marquee.min.js"></script>
<script>
    $(function () {
        $('.marquee').marquee({
            duration: 5000
        });
    });
</script>