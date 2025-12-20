<?
$getBlueVisit = '<script type="text/javascript" src="//event.getblue.io/js/blue-tag.min.js" async="true"></script>
                            <script type="text/javascript">
                                window.blue_q = window.blue_q || [];
                                window.blue_q.push(
                                    {event: "setCampaignId", value: "00817ED8-9D92-C9B7-14D7CD0015C22D53"}
                                    ,{event: "setPageType", value: "visit"}
                                );
                            </script>';
if( !$APPLICATION->GetPageProperty('offVisitGetBlue') ){
        echo $getBlueVisit;
}
?>