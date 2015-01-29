<?php

$tmpl->add(<<<API
<h1>Octagora API v1</h1>
<h2>Introduction</h2>
<p>Octagora is a service saving texts with tags</p>
<p>Octagora API v1 URL is "https://octagora.com/api/1"</p>
<h2>Reference</h2>
<h3>Messages<span class="uri">/messages</span></h3>
<div id="messagesget" class="method">
    <div id="getmessages" class="request">
        <h4><span class="method">GET</span>Get Messages</h4>
        <table class="parameters">
            <caption>Parameters</caption>
            <tr><th>Parameter</th><th>Required</th><th>Description</th><th>Default</th></tr>
            <tr><td>access_token</td>   <td class="red">Yes</td>    <td>ACCESS_TOKEN</td>                                                                       <td>-</td></tr>
            <tr><td>ts</td>             <td>No</td>                 <td>TAGS: <a href="#tags">Details</a></td>                                                  <td>""</td></tr>
            <tr><td>o</td>              <td>No</td>                 <td>ORDER : 'asc' to ascending order, 'desc' to descending order.</td>                      <td>"desc"</td></tr>
            <tr><td>s</td>              <td>No</td>                 <td>START: Start getting after this number.</td>                                            <td>"0"</td></tr>
            <tr><td>m</td>              <td>No</td>                 <td>MAX: Maximum message number.</td>                                                       <td>"100"</td></tr>
            <tr><td>n</td>              <td>No</td>                 <td>NEED: Select 'i'(id) 't'(text) 'c'(created) 'ts'(tags), comma delimited.</td>           <td>"i,t"</td></tr>
            <tr><td>tn</td>             <td>No</td>                 <td>TAGNEED: Select tag qualifiers(<a href="#qualifier">detail</a>), comma delimited</td> <td>"normal,by_user,to_user,user,message,to_message"</td></tr>
            <tr><td>l</td>              <td>No</td>                 <td>LAST: Exclude older than this number.</td>                                              <td>""</td></tr>
        </table>
    </div>
    <div id="getamessage" class="request">
        <h4><span class="method">GET</span>Get a Message</h4>
    </div>
</div>
<div id="messagespost" class="method">
    <div id="postamessage" class="request">
        <h4><span class="method">POST</span>Post a Message</h4>
    </div>
</div>
<div id="tags">
    <h4>Tags</h4>
    <p>Escape '*', '.', '!', '_', '(', ')', ':', '/' in tag text by '/'.<span class="example">Example: "Example Tag(._.)" to "Example Tag/(/./_/./)".</span></p>
    <table>
        <caption>Operators</caption>
        <tr><th>Operator</th>   <th>Description</th></tr>
        <tr><td>*</td>          <td>AND</td></tr>
        <tr><td>.</td>          <td>OR</td></tr>
        <tr><td>!</td>          <td>NOT</td></tr>
        <tr><td>_</td>          <td>XOR</td></tr>
    </table>
    <p><span class="example">a*!(b.c)*!d</span></p>
    <p>It means "having TAG 'a', and not having TAG 'b' or TAG 'c', and having TAG other than 'd'."</p>
    <p>'~' usually means NOT. But if it locates just before a TAG, it means "having tags other than the TAG."</p>
</div>
<div id="qualifier">
    <h4>Tag Qualifier</h4>
    <p>Tag Qualifier is like "message:MESSAGE_ID".</p>
    <table>
        <caption>Tag Qualifiers</caption>
        <tr><th>Tag Qualifier</th><th>Type</th><th>Subject</th><th>Description</th></tr>
        <tr><td>normal</td><td>DEFAULT</td><th>Anything is OK.</th><td>Normal tag. You will not use this.</td></tr>
        <tr><td>year, month, day, hour, minute</td><td>AUTO</td><th>4 digits for year, zero-filled 2 digits for others.</th><td>A posting time</td></tr>
        <tr><td>by_user</td><td>AUTO</td><th>User_ID</th><td>User who posted.</td></tr>
        <tr><td>user, to_user</td><td>OPTION</td><th>User_ID</th><td>Relate to a user, Reply to a user</td></tr>
        <tr><td>message, to_message</td><td>OPTION</td><th>Message_ID</th><td>Relate to a message, Reply to a message</td></tr>
        <tr><td>length</td><td>AUTO</td><th>integer</th><td>A text length</td></tr>
        <tr><td>application</td><td>AUTO</td><th>Application_ID</th><td>A posted Client</td></tr>
        <tr><td>hash</td><td>OPTION</td><th>Anything is OK.</th><td>Hash a string.</td></tr>
        <tr><td>not_used</td><td>AUTO</td><th>Tag Qualifier</th><td>A not used tag qualifier</td></tr>
    </table>
    <ul>
    <li>Type DEFAULT is used all not tag qualifiers</li>
    <li>Type AUTO is created by server, and unavailable when posting.</li>
    <li>Type OPTION is available when posting.</li>
    </ul>
</div>
API
);
