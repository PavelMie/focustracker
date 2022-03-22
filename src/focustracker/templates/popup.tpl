<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>

<style>
    #overlay {
        display: none;
        position: fixed;
        top: 0;
        bottom: 0;
        background: #999;
        width: 100%;
        height: 100%;
        opacity: 0.8;
        z-index: 1000;
        overflow: hidden;
    }

    #popup {
    {$background} background-repeat: no-repeat;
        background-size: 100% 100%;
        border: solid 5px white;
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        width: 500px;
        height: 250px;
        margin-left: -250px;
        margin-top: -250px;
        z-index: 1001;
    }

    #popupclose {
        color: white;
        float: right;
        padding: 10px;
        cursor: pointer;
    }

    .popupcontent {
        color: white;
        padding: 20px;
    }
</style>

<div id="overlay" class="overlay"></div>
<div id="popup">
    <div class="popupcontrols">
        <span id="popupclose">X</span>
    </div>
    <div class="popupcontent">
        {$popup_text}
    </div>
</div>
<script type="text/javascript">

    // Initialize Variables
    var closePopup = document.getElementById("popupclose");
    var overlay = document.getElementById("overlay");
    var popup = document.getElementById("popup");

    // Close Popup Event
    closePopup.onclick = function () {
        overlay.style.display = 'none';
        popup.style.display = 'none';
        document.getElementById("index").style.overflow = 'scroll';
        if ({$break} >
        0
    )
        {
            var date = new Date();
            var newdate = new Date(date.getTime() + {$break} * 60000);
            document.cookie = "popup_break=1; expires=" + newdate + ";path=/";
        }
    };

    function getCookie(cname) {
        let name = cname + "=";
        let decodedCookie = decodeURIComponent(document.cookie);
        let ca = decodedCookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    $(document).mouseleave(function () {
        if (getCookie("popup_break") === "") {
            overlay.style.display = 'block';
            popup.style.display = 'block';
            document.getElementById("index").style.overflow = 'hidden';
        }
    });
</script>
