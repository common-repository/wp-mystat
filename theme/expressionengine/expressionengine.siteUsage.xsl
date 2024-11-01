<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:import href="expressionengine.xsl" />
  <xsl:output method="html"/>
  <xsl:template name="content">
    <xsl:call-template name="graphic"/>
    <table class="table table-striped table-bordered">
      <thead>
        <tr>
          <th class="manage-column" rowspan="2"></th>
          <th class="manage-column" colspan="2" style="text-align:center;width:300px"><xsl:value-of select="//REPORT/TRANSLATE/ROBOT"/></th>
          <th class="manage-column" colspan="2" style="text-align:center;width:300px"><xsl:value-of select="//REPORT/TRANSLATE/USER"/></th>
        </tr>
        <tr>
          <th class="manage-column" style="text-align:center;"><xsl:value-of select="//REPORT/TRANSLATE/UNIQ"/></th>
          <th class="manage-column" style="text-align:center;"><xsl:value-of select="//REPORT/TRANSLATE/VIEW"/></th>
          <th class="manage-column" style="text-align:center;"><xsl:value-of select="//REPORT/TRANSLATE/UNIQ"/></th>
          <th class="manage-column" style="text-align:center;"><xsl:value-of select="//REPORT/TRANSLATE/VIEW"/></th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <th><xsl:value-of select="//REPORT/TRANSLATE/AVERAGE"/></th>
          <th style="text-align:center;"><xsl:value-of select="format-number(sum(//REPORT/INDICATORS/INDICATOR/ROBOT/UNIQ) div count(//REPORT/INDICATORS/INDICATOR/ROBOT/UNIQ),'#.##')"/></th>
          <th style="text-align:center;"><xsl:value-of select="format-number(sum(//REPORT/INDICATORS/INDICATOR/ROBOT/VIEW) div count(//REPORT/INDICATORS/INDICATOR/ROBOT/VIEW),'#.##')"/></th>
          <th style="text-align:center;"><xsl:value-of select="format-number(sum(//REPORT/INDICATORS/INDICATOR/USER/UNIQ) div count(//REPORT/INDICATORS/INDICATOR/USER/UNIQ),'#.##')"/></th>
          <th style="text-align:center;"><xsl:value-of select="format-number(sum(//REPORT/INDICATORS/INDICATOR/USER/VIEW) div count(//REPORT/INDICATORS/INDICATOR/USER/VIEW),'#.##')"/></th>
        </tr>
        <tr>
          <th><xsl:value-of select="//REPORT/TRANSLATE/TOTAL"/></th>
          <th style="text-align:center;"><xsl:value-of select="sum(//REPORT/INDICATORS/INDICATOR/ROBOT/UNIQ)"/></th>
          <th style="text-align:center;"><xsl:value-of select="sum(//REPORT/INDICATORS/INDICATOR/ROBOT/VIEW)"/></th>
          <th style="text-align:center;"><xsl:value-of select="sum(//REPORT/INDICATORS/INDICATOR/USER/UNIQ)"/></th>
          <th style="text-align:center;"><xsl:value-of select="sum(//REPORT/INDICATORS/INDICATOR/USER/VIEW)"/></th>
        </tr>
      </tfoot>
      <tbody>
        <xsl:variable name="maxUniq">
          <xsl:call-template name="maximum">
            <xsl:with-param name="pSequence" select="//REPORT/INDICATORS/INDICATOR/USER/UNIQ"/>
          </xsl:call-template>
        </xsl:variable>
        <xsl:variable name="minUniq">
          <xsl:call-template name="minimum">
            <xsl:with-param name="pSequence" select="//REPORT/INDICATORS/INDICATOR/USER/UNIQ"/>
          </xsl:call-template>
        </xsl:variable>
        <xsl:for-each select="//REPORT/INDICATORS/INDICATOR">
          <tr>
            <xsl:if test="//REPORT/PERIOD/START != //REPORT/PERIOD/END">
               <xsl:attribute name="style">cursor:pointer;</xsl:attribute>
               <xsl:attribute name="onclick">showDayStat(this,<xsl:value-of select="TIMESTAMP"/>);return false;</xsl:attribute>
            </xsl:if>
            <td>
              <xsl:if test="HOLIDAY = 1">
                <xsl:attribute name="style">color:red;</xsl:attribute>
              </xsl:if>
              <xsl:value-of select="NAME"/>
            </td>
            <td class="center"><xsl:value-of select="ROBOT/UNIQ"/></td>
            <td class="center"><xsl:value-of select="ROBOT/VIEW"/></td>
            <td class="center">
              <xsl:if test="$maxUniq=USER/UNIQ">
                <xsl:if test="USER/UNIQ&gt;0">
                  <xsl:attribute name="style">background-color:#efe;color:#0f0;font-weight:bold;</xsl:attribute>
                </xsl:if>
              </xsl:if>
              <xsl:if test="$minUniq=USER/UNIQ">
                <xsl:if test="USER/UNIQ&gt;0">
                  <xsl:attribute name="style">background-color:#fee;color:#f00;font-weight:bold;</xsl:attribute>
                </xsl:if>
              </xsl:if>
              <xsl:value-of select="USER/UNIQ"/>
            </td>
            <td class="center"><xsl:value-of select="USER/VIEW"/></td>
          </tr>
          <tr></tr>
          <tr>
            <xsl:if test="$maxUniq&gt;0">
              <td colspan="5" style="padding:0px;">
                <div class="progress">
                  <div class="bar">
                    <xsl:attribute name="style">width:<xsl:value-of select='USER/UNIQ * 100 div sum(//REPORT/INDICATORS/INDICATOR/USER/UNIQ)'/>%</xsl:attribute>
                    <xsl:value-of select='format-number(USER/UNIQ * 100 div sum(//REPORT/INDICATORS/INDICATOR/USER/UNIQ),"#.##")'/>%
                  </div>
                </div>
              </td>
            </xsl:if>
          </tr>
        </xsl:for-each>
      </tbody>
    </table>
  </xsl:template>
  <xsl:template name="graphicjsondata">
    [
      <xsl:if test="count(//REPORT/INDICATORS/INDICATOR) > 0">
        <xsl:for-each select="//REPORT/INDICATORS/INDICATOR">
          [new Date(<xsl:value-of select="TIMESTAMP"/> * 1000),<xsl:value-of select="USER/UNIQ"/>,<xsl:value-of select="USER/VIEW"/>,<xsl:value-of select="ROBOT/UNIQ"/>,<xsl:value-of select="ROBOT/VIEW"/>]<xsl:if test="position() != last()">,</xsl:if>
        </xsl:for-each>
      </xsl:if>
    ]
  </xsl:template>
  <xsl:template name="graphic">
    <div id="mystat_graphic" class="well well-small span12"></div>
    <script type="text/javascript"><![CDATA[
      if(typeof google != 'undefined' && typeof google.visualization == 'undefined'){
        google.charts.load('current',{'mapsApiKey':'AIzaSyAkz6JyMuz7MB3cicc9GbQIZwK4GgAynwo','packages':['corechart'], 'language':']]><xsl:value-of select="//REPORT/LANGUAGE"/><![CDATA['});
        google.charts.setOnLoadCallback(viewChart);
      }
      function viewChart(){
        if(typeof google == 'undefined' || typeof google.visualization == 'undefined' || typeof google.visualization.DataTable == 'undefined'){return;}
        var data = new google.visualization.DataTable();
        data.addColumn('datetime', '');
        data.addColumn('number', "]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="concat(//REPORT/TRANSLATE/USER,': ',//REPORT/TRANSLATE/UNIQ)"/></xsl:call-template><![CDATA[");
        data.addColumn('number', "]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="concat(//REPORT/TRANSLATE/USER,': ',//REPORT/TRANSLATE/VIEW)"/></xsl:call-template><![CDATA[");
        data.addColumn('number', "]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="concat(//REPORT/TRANSLATE/ROBOT,': ',//REPORT/TRANSLATE/UNIQ)"/></xsl:call-template><![CDATA[");
        data.addColumn('number', "]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="concat(//REPORT/TRANSLATE/ROBOT,': ',//REPORT/TRANSLATE/VIEW)"/></xsl:call-template><![CDATA[");
        data.addRows(]]><xsl:call-template name="graphicjsondata"/><![CDATA[);
        var options = {
          height: 400,
          legend: {
            position: 'none'
          },
          vAxis: {
            format: '#'
          },
          dataOpacity: 0.9,
          theme: 'maximized',
          focusTarget: 'category'
        };
        var chart = new google.visualization.ColumnChart(document.getElementById('mystat_graphic'));
        chart.draw(data, options);
      }
      function showDayStat(el,time){
        loadDate(']]><xsl:value-of select="//REPORT/REPORT"/><![CDATA[',moment(new Date(time * 1000)).format('DD.MM.YYYY'),moment(new Date(time * 1000)).format('DD.MM.YYYY'));
      }
    ]]></script>
  </xsl:template>
</xsl:stylesheet>