<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:import href="../template.xsl" />
  <xsl:output method="html"/>
  <xsl:template match="/">
    <style type="text/css">
      @font-face {
        font-family: 'mystat';
        src: url('<xsl:value-of select="//REPORT/PATHTOASSET"/>mystat.eot');
        src: url('<xsl:value-of select="//REPORT/PATHTOASSET"/>mystat.eot?#iefix') format('embedded-opentype'),
             url('<xsl:value-of select="//REPORT/PATHTOASSET"/>mystat.woff2') format('woff2'),
             url('<xsl:value-of select="//REPORT/PATHTOASSET"/>mystat.woff') format('woff'),
             url('<xsl:value-of select="//REPORT/PATHTOASSET"/>mystat.ttf') format('truetype'),
             url('<xsl:value-of select="//REPORT/PATHTOASSET"/>mystat.svg#mystatregular') format('svg');
        font-weight: normal;
        font-style: normal;
      }
      body{cursor:default;}
      .wrap .icon{font-family:mystat;text-shadow: 1px 1px 3px rgba(150, 150, 150, 0.81);}
      .wrap #mystat_graphic{margin-bottom:10px;padding:20px;}
      .wrap .notice, .wrap .updated{display:none;}
      .wrap .notice.notice-warning{display:block;margin-left: 125px;}
      .wrap .spinner{visibility:visible;display:none;margin:3px 10px 0;}
      .wrap .button-small .spinner{margin: 1px 10px 0;}
      .wrap .spinner.inline{float:none;margin: -6px 10px;}
      .wrap .wp-filter{margin:0;margin-bottom:10px;}
      .wrap .wp-filter.bottom{padding: 5px;}
      .wrap .button{white-space:normal;}
      .wrap .wp-filter .period{float:left;padding:10px 0;line-height:27px;}
      .wrap .wp-filter .period .button{white-space:normal;}
      .wrap .wp-filter .period .text{line-height:28px;margin-right:20px;}
      .wrap .wp-filter .topbtn{float:right;padding:1px 0;}
      .wrap .wp-filter .topbtn .button32{white-space:normal;height: 45px;padding: 6px;}
      .wrap .wp-filter .bottombtn{padding:1px 0;}
      .wrap .wp-filter .bottombtn .button32{white-space:normal;height: 45px;padding: 6px;margin:0 2px;}
      .wrap .icon.button32{font-size:32px;}
      .wrap .pressthis,.wrap .pressthis-bookmarklet{margin: 20px 0;margin-left:125px;}
      .wrap .pressthis a, .wrap .pressthis-bookmarklet a{cursor:default;}
      .wrap .pressthis a:after, .wrap .pressthis-bookmarklet:after{width: 130px;}
      .wrap .postbox-container .postbox{min-width: 197px;}
      .wrap .postbox-container .hndle{cursor:pointer;}
      .wrap .postbox-container .inside .button{width:100%;}
      .wrap .progress{height: 10px;width: 100%;line-height: 2em;padding: 0;overflow: hidden;border-radius: 22px;background: #DDD;box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);}
      .wrap .progress .percent{position: relative;width: 100%;padding: 0;font-size: 9px;color: #FFF;text-align: center;line-height: 11px;font-weight: 400;text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);}
      .wrap .progress .bar{width: 0;height: 100%;margin-top: -11px;border-radius: 22px;background-color: #0074A2;box-shadow: inset 0 0 2px rgba(0, 0, 0, 0.3);}
      .wrap #dashboard-widgets{margin-top:10px;}
      .wrap #dashboard-widgets .postbox-container:first-child{margin-right: 1%;}
      .wrap #dashboard-widgets .postbox-container .postbox{padding: 15px;}
      .wrap #logo{position:absolute;left:0px;width:115px;height:115px;}
      .wrap .maintitle{margin-left:125px;}
      .wrap .tablenav .tablenav-pages .spinner{float:left;margin-top: 6px;}
      .wrap .tablenav .tablenav-pages .button-page{font-size: 12px;padding: 7px 9px;line-height: 1;}
      .wrap .tablenav .tablenav-pages .active-page{font-size: 12px;padding: 7px 9px;line-height: 1;color: #FFF;background: #2EA2CC;}
      .wrap .tablenav .tablenav-pages .sep-dots{padding: 0px 8px;}
      .wrap .widefat{table-layout: fixed;margin-bottom: 20px;}
      .wrap .widefat td{text-overflow: ellipsis;overflow: hidden;white-space: nowrap;}
      .wrap .screen{border:2px solid #aaa;background-color:#ccc;}
      .date-picker-wrapper .drp_top-bar .apply-btn{background: #2EA2CC;border-color: #0074A2;-webkit-box-shadow: inset 0 1px 0 rgba(120, 200, 230, 0.5),0 1px 0 rgba(0, 0, 0, 0.15);box-shadow: inset 0 1px 0 rgba(120, 200, 230, 0.5),0 1px 0 rgba(0, 0, 0, 0.15);color: #FFF;text-decoration: none;}
      @media only screen and (min-width:1500px){#wpbody-content #dashboard-widgets .postbox-container{width:49.5% !important;}
    </style>
    <div class="wrap">
      <div id="logo" onclick="logoSVG.setAnimation(true);"></div>
      <script type="text/javascript">logoSVG.setSize(115).setAnimation(false).setElementId('logo').run();</script>
      <h1 class="maintitle"><xsl:value-of select="//REPORT/TITLE"/></h1>
      <p class="pressthis pressthis-bookmarklet"><a><span><xsl:value-of select="//REPORT/SUBTITLE"/></span></a></p>
      <div id="poststuff">
        <div id="post-body" class="columns-2">
          <div id="post-body-content" style="position: relative;">
            <div class="wp-filter">
              <xsl:if test="//REPORT/BUTTON_HIDE/PERIOD">
                <xsl:attribute name="style">display:none;</xsl:attribute>
              </xsl:if>
              <div class="period"><span class="text"><xsl:value-of select="//REPORT/TRANSLATE/PERIODREPORT"/></span> <a class="button" data-range="{//REPORT/PERIOD/START} - {//REPORT/PERIOD/END}" id="dataselectrange"><span class="data"><xsl:value-of select="//REPORT/PERIOD/START"/> - <xsl:value-of select="//REPORT/PERIOD/END"/></span> <span class="spinner"></span></a></div>
              <div class="topbtn"></div>
            </div>
            <xsl:call-template name="content"/>
            <xsl:if test="not(//REPORT/BUTTON_HIDE/EXPORT)">
              <div class="wp-filter bottom">
                <div class="bottombtn">
                  <button class="button button32 icon" title="{//REPORT/TRANSLATE/EXPORTXML}" onclick="document.location='{//REPORT/PATHEXPORT}&amp;report=export&amp;type=xml&amp;in={//REPORT/REPORT}';">&#40;</button>
                </div>
              </div>
            </xsl:if>
          </div>
          <div id="postbox-container-1" class="postbox-container">
            <div id="side-sortables" class="meta-box-sortables ui-sortable" style="">
              <xsl:call-template name="menu"/>
            </div>
          </div>
        </div>    
      </div>
    </div>
    <script type="text/javascript"><![CDATA[
      function loadDate(report,dateStart,dateEnd,param,callback){
        logoSVG.setAnimation(true);
        jQuery.ajax({
          url: ajaxurl,
          data: {
            action: 'mystat',
            report: report,
            datestart: dateStart,
            dateend: dateEnd,
            ajax: typeof callback =='function'?true:false,
            param: param
          },
          dataType: typeof callback =='function'?'json':'html',
          type: 'POST',
          success: function(data, textStatus){
            logoSVG.setAnimation(false);
            if(typeof callback =='function'){
              callback(data, textStatus);
              return true;
            }
            logoSVG.runtime = false;
            if(typeof viewChart !='undefined'){
              delete viewChart;
              viewChart = undefined;
            }
            jQuery('#mystat').html(data);
            jQuery(document).scrollTop(0);
            if(typeof viewChart !='undefined'){
              setTimeout(function(){
                viewChart();
              },100);
            }
          },
          error: function(){
            logoSVG.runtime = false;
            document.location.reload();
          }
        });
      }
      function loadPage(page){
        var spin = jQuery('.wrap .tablenav .tablenav-pages .spinner');
        if(spin.length>0){spin.show();}
        var ddt = jQuery('#dataselectrange').data('range').split(' - ');
        loadDate(']]><xsl:value-of select="//REPORT/REPORT"/><![CDATA[',ddt[0],ddt[1],{page:page});
      }
      function loadAjax(param,callback){
        var ddt = jQuery('#dataselectrange').data('range').split(' - ');
        loadDate(']]><xsl:value-of select="//REPORT/REPORT"/><![CDATA[',ddt[0],ddt[1],param,callback);
      }
      function selectMenu(el,report){
        var ddt = jQuery('#dataselectrange').data('range').split(' - ');
        if(!jQuery(el).parent().children('.spinner').is(':visible')){
          jQuery(el).width(jQuery(el).width()-40);
          jQuery(el).parent().children('.spinner').show();
          loadDate(report,ddt[0],ddt[1]);
        }
      }
      jQuery(document).ready(function($){
        if(typeof viewChart !='undefined'){
          viewChart();
        }
        $(window).on('load',function(){
          if(typeof viewChart !='undefined'){
            viewChart();
          }
        });
        $(window).resize(function(){
          if(typeof viewChart !='undefined'){
            viewChart();
          }
        });
        $('.wrap .postbox-container .hndle, .wrap .postbox-container .handlediv').click(function(){
          $(this).parent('.postbox').toggleClass('closed');
        });
        $('#dataselectrange').dateRangePicker({
          showShortcuts: true,
          shortcuts: {
            'next-days': null,
            'next': null,
            'prev-days': [1,7,30],
            'prev' : ['week','month']
          },
          separator: ' - ',
          language: ']]><xsl:value-of select="translate(//REPORT/LANGUAGE,'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz')"/><![CDATA[',
          format: 'DD.MM.YYYY',
          endDate: new Date(]]><xsl:value-of select="//REPORT/TIME"/><![CDATA[ * 1000),
          showPrevMonth: true,
          startOfWeek: 'monday',
          minDays: 1,
          maxDays: 365,
          getValue: function(){
            return $('#dataselectrange .data').html();
          },
          setValue: function(s){
            $('#dataselectrange .data').html(s);
          }
        }).bind('datepicker-close',function(event,obj){
          if(obj.value!=$('#dataselectrange').attr('data-range')){
            $('#dataselectrange .spinner').show();
            loadDate(']]><xsl:value-of select="//REPORT/REPORT"/><![CDATA[',moment(obj.date1).format('DD.MM.YYYY'),moment(obj.date2).format('DD.MM.YYYY'));
            $('#dataselectrange').data('dateRangePicker').destroy();
          }
        });
      });
    ]]></script>
  </xsl:template>
  <xsl:template name="content"></xsl:template>
  <xsl:template name="menu">
    <xsl:for-each select="//REPORT/MENU">
      <div class="postbox">
        <div class="handlediv"><br/></div>
        <h3 class="hndle"><span><xsl:value-of select="TITLE"/></span></h3>
        <div class="inside">
          <ul>
            <xsl:for-each select="ITEM">
            <li>
              <div>
              <span class="spinner"></span>
              <a class="button button-small">
                <xsl:if test="not(@disabled = 1)">
                  <xsl:attribute name="onclick">selectMenu(this,'<xsl:value-of select="@code"/>');return false;</xsl:attribute>
                </xsl:if>
                <xsl:if test="@disabled = 1">
                  <xsl:attribute name="class">button button-small disabled</xsl:attribute>
                </xsl:if>
                <xsl:if test="//REPORT/REPORT = @code">
                  <xsl:attribute name="class">button button-small button-primary</xsl:attribute>
                </xsl:if>
                <xsl:value-of select="."/>
              </a>
              </div>
            </li>
            </xsl:for-each>
          </ul>
        </div>
      </div>
    </xsl:for-each>
  </xsl:template>
  <xsl:template name="pagination">
  	<xsl:param name="recordsPerPage"/>
  	<xsl:param name="records"/>
    <xsl:param name="currentPage" select="1"/>
    <xsl:param name="showAlwaysFirstAndLast" select="true"/>
    <xsl:variable name="numberOfRecords" select="count($records)"/>
    <xsl:variable name="lastPage" select="ceiling($numberOfRecords div $recordsPerPage)"/>
    <xsl:variable name="extremePagesLimit" select="3"/>
    <xsl:variable name="nearbyPagesLimit" select="2"/>
    <xsl:if test="$lastPage &gt; 1">
      <div class="tablenav bottom">
        <div class="tablenav-pages">
          <span class="spinner"></span>
          <span class="pagination-links">
            <xsl:choose>
              <xsl:when test="$currentPage &gt; 1">
                <a class="first-page" href="" onclick="loadPage(1);return false;">«</a>
                <a class="prev-page" href="" onclick="loadPage({$currentPage - 1});return false;">‹</a>
                <xsl:for-each select="$records">
                  <xsl:if test="position() &lt;= $extremePagesLimit">
                    <xsl:if test="position() &lt; $currentPage - $nearbyPagesLimit">
                      <a class="button-page" href="" onclick="loadPage({position()});return false;"><xsl:value-of select="position()"/></a>
                    </xsl:if>
                  </xsl:if>
                </xsl:for-each>
                <xsl:if test="$extremePagesLimit + 1 &lt; $currentPage - $nearbyPagesLimit">
                  <span class="sep-dots">...</span>
                </xsl:if>
                <xsl:for-each select="$records">
                  <xsl:if test="position() &gt;= $currentPage - $nearbyPagesLimit">
                    <xsl:if test="position() &lt;= $currentPage - 1">
                      <a class="button-page" href="" onclick="loadPage({position()});return false;"><xsl:value-of select="position()"/></a>
                    </xsl:if>
                  </xsl:if>
                </xsl:for-each>
              </xsl:when>
              <xsl:otherwise>
                <xsl:if test="$showAlwaysFirstAndLast = 'true'">
                  <a class="first-page disabled">«</a>
                  <a class="prev-page disabled">‹</a>
                </xsl:if>
              </xsl:otherwise>
            </xsl:choose>
            <a class="active-page" onclick="loadPage({$currentPage});return false;"><xsl:value-of select="$currentPage"/></a>
            <xsl:choose>
              <xsl:when test="$currentPage &lt; $lastPage">
                <xsl:for-each select="$records">
                  <xsl:if test="position() &gt;= $currentPage + 1">
                    <xsl:if test="position() &lt;= $currentPage + $nearbyPagesLimit">
                      <xsl:if test="position() &lt;= $lastPage">
                        <a class="button-page" href="" onclick="loadPage({position()});return false;"><xsl:value-of select="position()"/></a>
                      </xsl:if>
                    </xsl:if>
                  </xsl:if>
                </xsl:for-each>
                <xsl:if test="($lastPage - $extremePagesLimit) &gt; ($currentPage + $nearbyPagesLimit)">
                  <span class="sep-dots">...</span>
                </xsl:if>
                <xsl:for-each select="$records">
                  <xsl:if test="position() &gt;= $lastPage - $extremePagesLimit + 1">
                    <xsl:if test="position() &lt;= $lastPage">
                      <xsl:if test="position() &gt; $currentPage + $nearbyPagesLimit">
                        <a class="button-page" href="" onclick="loadPage({position()});return false;"><xsl:value-of select="position()"/></a>
                      </xsl:if>
                    </xsl:if>
                  </xsl:if>
                </xsl:for-each>
                <a class="next-page" href="" onclick="loadPage({$currentPage + 1});return false;">›</a>
                <a class="last-page" href="" onclick="loadPage({$lastPage});return false;">»</a>
              </xsl:when>
              <xsl:otherwise>
                <xsl:if test="$showAlwaysFirstAndLast = 'true'">
                  <a class="next-page disabled">›</a>
                  <a class="last-page disabled">»</a>
                </xsl:if>
              </xsl:otherwise>
            </xsl:choose>
          </span>
        </div>
        <br class="clear"/>
      </div>          
    </xsl:if>
  </xsl:template>
</xsl:stylesheet>