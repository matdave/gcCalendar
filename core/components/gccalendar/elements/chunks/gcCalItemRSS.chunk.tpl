<item>
    <title>[[+title]]</title>
    <link>[[~[[*parent]]?scheme=`https`]]</link>
    <description><![CDATA[<h2>[[+title]]</h2>
    <h3>[[+start:date=`%B %e`]] [[+end:date=`%B %e`:ne=`[[+start:date=`%B %e`]]`:then=`- [[+end:date=`%B %e`]]`]] [[+ad:ne=`1`:then=`<em>[[+start:date=`%l:%M %P`]] - [[+end:date=`%l:%M %P`]]</em>`]]</h3>
    <p>[[+link:ne=``:then=`<a href="[[+link]]" target="_blank">[[+link]]</a>`]]</p>
    [[+notes]]
    [[+locationname:ne=``:then=`<h4>[[+locationname]]</h4>`]]
    <p>
        [[+locationcontact:ne=``:then=`<strong>Contact</strong><br/> [[+locationcontact]]<br/>`]]
        [[+locationemail:ne=``:then=`<a href="mailto:[[+locationemail]]?Subject=Re: [[+title]]">[[+locationemail]]</a><br/>`]]
        [[+locationphone:ne=``:then=`[[+locationphone]]<br/>`]]
        [[+locationaddr:ne=``:then=`[[+locationaddr]]<br/>`]]
        [[+locationcity:ne=``:then=`[[+locationcity]], `]][[+locationstate:ne=``:then=`[[+locationstate]] `]][[+locationzip]]
    </p>]]></description>
    <pubDate>[[+pubDate]]</pubDate>
    <guid>[[+infoURL]]</guid>
</item>