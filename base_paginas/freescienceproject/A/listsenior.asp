<%@ Language=VBScript %>
<%
if NOT (session("logedin")="Y") then
   Response.Redirect "/index.asp"
end if
%>
<HTML>
<HEAD>
  <META NAME="GENERATOR" CONTENT="Microsoft FrontPage 4.0">
  <META NAME="keywords" CONTENT="Science, Project, ScienceProject, Sciencefair, Chemistry, electricity, Biology, easy, free, help">
  <META NAME="description" CONTENT="Help is available for your ScienceProject">
  <META NAME="author" CONTENT="ScienceProject.com, New Jersey Industrial Research Center Inc, Mohammad Hamzeh">
  <META NAME="publisher" CONTENT="ScienceProject.com">
  <META NAME="copyright" CONTENT="All Rights Reserved">
  <META NAME="robots" CONTENT="index,nofollow">
  <META NAME="revisit-after" CONTENT="1 days">
  <TITLE>Registered members of ScienceProject.com can earn credits that can be used toward free Science Kits.</TITLE>
  <STYLE type=text/css>
.smalllink {
	FONT-SIZE: 10px; COLOR: #990000; FONT-FAMILY: Verdana, Geneva, Arial, sans-serif; TEXT-DECORATION: none
}
.linkleft {
	FONT-WEIGHT: bold; FONT-SIZE: 12px; COLOR: #990000; FONT-FAMILY: Verdana, Geneva, Arial, sans-serif; TEXT-DECORATION: none
}
.linkleftoff {
	FONT-WEIGHT: bold; FONT-SIZE: 12px; COLOR: #000000; FONT-FAMILY: Verdana, Geneva, Arial, sans-serif
}
.body {
	FONT-SIZE: 13px; COLOR: #000000; FONT-FAMILY: Arial, Geneva, sans-serif
}
.bodycolor {
	COLOR: #993300
}
.bodysmall {
	FONT-SIZE: 10px; COLOR: #000000; FONT-FAMILY: Verdana, Geneva, Arial, sans-serif
}
.pullquotesmall {
	FONT-SIZE: 13px; COLOR: #663300; LINE-HEIGHT: 18px; FONT-FAMILY: Palatino, Times
}
.command {
	FONT-SIZE: 13px; COLOR: #663300; LINE-HEIGHT: 18px; FONT-FAMILY: Palatino, Times
}
.title {
	FONT-WEIGHT: bold; FONT-SIZE: 16px; COLOR: #993300; FONT-FAMILY: Verdana, Geneva, Arial, sans-serif
}
A:hover {
	TEXT-DECORATION: underline
}
A {
	TEXT-DECORATION: none
}
</STYLE>

</HEAD>
    
 
    
    
<BODY BGCOLOR="#ffffff">
 <CENTER>
<TABLE WIDTH="585" BORDER="0" CELLSPACING="0" CELLPADDING="0">
  <TR>
    <TD WIDTH="100%">
    <CENTER>
    <TABLE WIDTH="100%" BORDER="0" CELLSPACING="0" CELLPADDING="0">
      <TR>
        <TD WIDTH="612">
           <MAP NAME="ScienceProject7Map11">
           <AREA shape="rect" coords="69" HREF="/membership/loginfailed.asp">
           <AREA SHAPE="rect" COORDS="533, 2, 595, 62" HREF="/help.html">
           <AREA SHAPE="rect" COORDS="468, 3, 530, 62" HREF="/new.html">
           <AREA SHAPE="rect" COORDS="15, 5, 67, 59" HREF="mailto:info@scienceproject.com">
           </MAP><IMG SRC="/images/ScienceProject7.gif" WIDTH="600" HEIGHT="64"
           ALIGN="BOTTOM" BORDER="0" NATURALSIZEFLAG="3" USEMAP="#ScienceProject7Map11"
           ISMAP ALT="Welcome to ScienceProject.com">
        </TD>
      </TR>
      <TR>
        <TD WIDTH="612" BGCOLOR="#ff0033"></TD>
      </TR>
      <TR>
        <TD WIDTH="612"></TD>
      </TR>
      <TR>
        <TD WIDTH="612" BGCOLOR="#ff0000"></TD>
      </TR>


      <TR><TD>
      
      <table border="0" width="100%">
        <tr>
          <td valign="top" bgcolor="#FFDDDD">
             <!--#include virtual="/A/leftnav.asp" --></td>
          
          <td>
             <P><font size="2" face="Verdana">Senior projects are best for
             High school students. Many of the projects suggested in this
             section have no project guide and you might be the first person who
             works in such project. After you select a project, your project
             advisor will assist you to establish a clear guide line from the beginning
             and complete the project in an organized manner.</font></P>
             <P><font size="2" face="Verdana">(For ages 14 to 18)</font></P>
        <%
        Set rs=Server.CreateObject("ADODB.Recordset")
        str="Select * from projectinfo where id like'S%' order by id"
        rs.open str, conn
        While NOT rs.EOF
            title=rs("title")
            id=rs("id")
            response.write id &"   "&"<A HREF=/projects/intro/senior/"&id&".asp?t="&time()&">"& title &"</A><BR>"
            rs.MoveNext
            
        wend
        rs.close
        set rs=nothing
        
        %>

        
      
 </TABLE>


      <TR>
        <TD WIDTH="612" BGCOLOR="#ff0000"></TD>
      </TR>
      <TR>
        <TD WIDTH="612" BGCOLOR="#ff0000"></TD>
      </TR>


    </TABLE>
 




   
    <p align="center">
    <FONT SIZE="-2" FACE="Verdana">&nbsp;|<A HREF="help.html">Help</A> | <A HREF="terms.html">Terms of
    use</A> |</FONT><BR>
    
<!-- ************************************ START OF EXTRA LISTS ******************* -->

<TABLE WIDTH="600" BORDER="0" CELLSPACING="0" CELLPADDING="2">
  <TR>
    <TD WIDTH="100%">
        <b><font color="#FF5555" face="Verdana" size="-2">DISCLAIMER:<br>
        Although most of the experiments in this web-site are regarded as low
        hazard,<br>
        author and publisher expressly disclaim all liabilities for any
        occurrence, including, but<br>
        not limited to, damage, injury or death which might arise as a
        consequence of<br>
        the use of any information/experiments listed or described here.
        Therefore, you assume all liabilities and use such information at your
        own risk!</font></b>
    </TD>
  </TR>
</TABLE>

</TD>
</TR>
</TABLE>
  		<table align=center width=100% border=0>
        	<tr><td align=center>

<script type="text/javascript"><!--
google_ad_client = "pub-1421115494106695";
google_ad_width = 728;
google_ad_height = 90;
google_ad_format = "728x90_as";
google_ad_type = "text_image";
//2007-05-05: free science project
google_ad_channel = "8147074771";
google_color_border = "66B5FF";
google_color_bg = "FFFFFF";
google_color_link = "0000FF";
google_color_text = "000000";
google_color_url = "008000";
//-->
</script>
<script type="text/javascript"
  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>

				</td>
			</tr>
		</table>  

</BODY>
