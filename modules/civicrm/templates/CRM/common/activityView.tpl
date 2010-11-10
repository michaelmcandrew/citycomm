{literal}
<script type="text/javascript">
cj(document).ready(function(){
	cj("#activity-content").css({'overflow':'auto', 'width':'680px', 'height':'560px'});
});
	
function viewActivity( activityID, contactID ) {
    cj("#view-activity").show( );

    cj("#view-activity").dialog({
        title: "View Activity",
        modal: true, 
        width : 700,
        height : 650,
        resizable: true,
        bgiframe: true,
        overlay: { 
            opacity: 0.5, 
            background: "black" 
        },

        beforeclose: function(event, ui) {
            cj(this).dialog("destroy");
        },

        open:function() {
            cj("#activity-content").html("");
            var viewUrl = {/literal}"{crmURL p='civicrm/case/activity/view' h=0 q="snippet=4" }"{literal};
            cj("#activity-content").load( viewUrl + "&cid="+contactID + "&aid=" + activityID);
            
        },

        buttons: { 
            "Done": function() { 	    
                cj(this).dialog("close"); 
                cj(this).dialog("destroy"); 
            }
        }
    });
}
</script>
{/literal}
