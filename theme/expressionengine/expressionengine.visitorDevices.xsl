<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:import href="expressionengine.xsl" />
  <xsl:output method="html"/>
  <xsl:template name="content">

    <xsl:call-template name="pagination">
  		<xsl:with-param name="currentPage" select="//REPORT/INDICATORS/CURRENT_PAGE"/>
  		<xsl:with-param name="recordsPerPage" select="//REPORT/INDICATORS/PER_PAGE" />
  		<xsl:with-param name="records" select="//REPORT/INDICATORS/INDICATOR"/>
  	</xsl:call-template>

    <table class="table table-striped table-bordered">
      <thead>
        <tr>
          <th style="text-align:center;width:40px;">#</th>
          <th style="text-align:center;width:70px;">&#160;</th>
          <th class="manage-column" style="text-align:center;"><xsl:value-of select="//REPORT/TRANSLATE/DEVICE_NAME"/></th>
          <th class="manage-column" style="text-align:center;width:150px;"><xsl:value-of select="//REPORT/TRANSLATE/USER"/></th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <td colspan="3" class="manage-column"><xsl:value-of select="//REPORT/TRANSLATE/COUNT_DEVICE"/></td>
          <td class="manage-column" style="text-align:center;"><b><xsl:value-of select="count(//REPORT/INDICATORS/INDICATOR)"/></b></td>
        </tr>
        <tr>
          <td colspan="3"><xsl:value-of select="//REPORT/TRANSLATE/NODEVICEDETECT"/></td>
          <td style="text-align:center;"><b><xsl:value-of select="//REPORT/INDICATORS/NOTSET"/></b></td>
        </tr>
      </tfoot>
      <tbody>
        <xsl:variable name="maxUniq">
          <xsl:call-template name="maximum">
            <xsl:with-param name="pSequence" select="//REPORT/INDICATORS/INDICATOR/DEVICE/@count"/>
          </xsl:call-template>
        </xsl:variable>
        <xsl:variable name="minUniq">
          <xsl:call-template name="minimum">
            <xsl:with-param name="pSequence" select="//REPORT/INDICATORS/INDICATOR/DEVICE/@count"/>
          </xsl:call-template>
        </xsl:variable>
        <xsl:for-each select="//REPORT/INDICATORS/INDICATOR">
          <xsl:if test="position() &gt; //REPORT/INDICATORS/PER_PAGE * (//REPORT/INDICATORS/CURRENT_PAGE - 1)">
            <xsl:if test="position() &lt;= //REPORT/INDICATORS/PER_PAGE * //REPORT/INDICATORS/CURRENT_PAGE">
              <tr>
                <td align="center" style="vertical-align: middle;"><xsl:value-of select="position()"/>.</td>
                <td align="center" style="padding-top: 2px;padding-bottom: 2px;">
                  <xsl:if test="DEVICE/@flag != ''">
                    <img src="{//REPORT/PATHTOCACHE}{DEVICE/@flag}"/>
                  </xsl:if>
                </td>
                <td style="vertical-align: middle;">
                  <xsl:value-of select="DEVICE"/>
                </td>
                <td style="vertical-align: middle;" align="center">
                  <xsl:value-of select="DEVICE/@count"/>
                </td>
              </tr>
              <tr></tr>
              <tr>
                <xsl:if test="$maxUniq&gt;0">
                  <td colspan="4" style="padding: 0px;">
                    <div class="progress">
                      <div class="bar">
                        <xsl:attribute name="style">width:<xsl:value-of select='DEVICE/@count * 100 div sum(//REPORT/INDICATORS/INDICATOR/DEVICE/@count)'/>%</xsl:attribute>
                        <xsl:value-of select='format-number(DEVICE/@count * 100 div sum(//REPORT/INDICATORS/INDICATOR/DEVICE/@count),"#.##")'/>%
                      </div>
                    </div>
                  </td>
                </xsl:if>
              </tr>
            </xsl:if>
          </xsl:if>
        </xsl:for-each>
      </tbody>
    </table>

    <xsl:call-template name="pagination">
  		<xsl:with-param name="currentPage" select="//REPORT/INDICATORS/CURRENT_PAGE"/>
  		<xsl:with-param name="recordsPerPage" select="//REPORT/INDICATORS/PER_PAGE" />
  		<xsl:with-param name="records" select="//REPORT/INDICATORS/INDICATOR"/>
  	</xsl:call-template>

  </xsl:template>

</xsl:stylesheet>