<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:import href="expressionengine.xsl" />
  <xsl:output method="html"/>
  <xsl:template name="content">
    <xsl:call-template name="graphic"/>
    <table class="table table-striped table-bordered">
      <thead>
        <tr>
          <th rowspan="2" class="manage-column" style="text-align:center;"><xsl:value-of select="//REPORT/TRANSLATE/HOST"/></th>
          <th colspan="2" class="manage-column" style="text-align:center;width:300px;"><xsl:value-of select="//REPORT/TRANSLATE/ROBOT"/></th>
          <th colspan="2" class="manage-column" style="text-align:center;width:300px;"><xsl:value-of select="//REPORT/TRANSLATE/USER"/></th>
        </tr>
        <tr>
          <th class="manage-column" style="text-align:center;"><xsl:value-of select="//REPORT/TRANSLATE/UNIQ"/></th>
          <th class="manage-column" style="text-align:center;"><xsl:value-of select="//REPORT/TRANSLATE/VIEW"/></th>
          <th class="manage-column" style="text-align:center;"><xsl:value-of select="//REPORT/TRANSLATE/UNIQ"/></th>
          <th class="manage-column" style="text-align:center;"><xsl:value-of select="//REPORT/TRANSLATE/VIEW"/></th>
        </tr>
      </thead>
      <tfoot>
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
            <td>
              <xsl:value-of select="HOST"/>
            </td>
            <td class="center">
              <xsl:value-of select="ROBOT/UNIQ"/>
            </td>
            <td class="center">
              <xsl:value-of select="ROBOT/COUNT"/>
            </td>
            <td class="center">
              <xsl:value-of select="USER/UNIQ"/>
            </td>
            <td class="center">
              <xsl:value-of select="USER/COUNT"/>
            </td>
          </tr>
          <tr>
            <xsl:if test="$maxUniq&gt;0">
              <td colspan="5" style="padding: 0px;">
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
          ["<xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="HOST"/></xsl:call-template>",<xsl:value-of select="USER/UNIQ"/>]<xsl:if test="position() != last()">,</xsl:if>
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
        data.addColumn('string', '');
        data.addColumn('number', "]]><xsl:call-template name="escapeQuote"><xsl:with-param name="pText" select="concat(//REPORT/TRANSLATE/USER,': ',//REPORT/TRANSLATE/UNIQ)"/></xsl:call-template><![CDATA[");
        data.addRows(]]><xsl:call-template name="graphicjsondata"/><![CDATA[);
        var options = {
          height: 400,
          vAxis: {
            format: '#'
          },
          dataOpacity: 0.9,
          focusTarget: 'category'
        };
        var chart = new google.visualization.PieChart(document.getElementById('mystat_graphic'));
        chart.draw(data, options);
      }
    ]]></script>
  </xsl:template>
</xsl:stylesheet>