<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:import href="wordpress.xsl" />
  <xsl:output method="html"/>
  <xsl:template name="content">

    <xsl:call-template name="pagination">
  		<xsl:with-param name="currentPage" select="//REPORT/INDICATORS/CURRENT_PAGE"/>
  		<xsl:with-param name="recordsPerPage" select="//REPORT/INDICATORS/PER_PAGE" />
  		<xsl:with-param name="records" select="//REPORT/INDICATORS/INDICATOR"/>
  	</xsl:call-template>

    <table class="widefat">
      <thead>
        <tr>
          <th style="text-align:center;width:40px;">#</th>
          <th style="text-align:center;width:40px;">&#160;</th>
          <th class="manage-column" style="text-align:center;"><xsl:value-of select="//REPORT/TRANSLATE/ACCEPT_LANGUAGE"/></th>
          <th class="manage-column" style="text-align:center;width:150px;"><xsl:value-of select="//REPORT/TRANSLATE/USER"/></th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <td colspan="3" class="manage-column"><xsl:value-of select="//REPORT/TRANSLATE/COUNT_LANG"/></td>
          <td class="manage-column" style="text-align:center;"><b><xsl:value-of select="count(//REPORT/INDICATORS/INDICATOR)"/></b></td>
        </tr>
        <tr>
          <td colspan="3"><xsl:value-of select="//REPORT/TRANSLATE/NOLANGDETECT"/></td>
          <td style="text-align:center;"><b><xsl:value-of select="//REPORT/INDICATORS/NOTSET"/></b></td>
        </tr>
      </tfoot>
      <tbody>
        <xsl:variable name="maxUniq">
          <xsl:call-template name="maximum">
            <xsl:with-param name="pSequence" select="//REPORT/INDICATORS/INDICATOR/LANGUAGE/@count"/>
          </xsl:call-template>
        </xsl:variable>
        <xsl:variable name="minUniq">
          <xsl:call-template name="minimum">
            <xsl:with-param name="pSequence" select="//REPORT/INDICATORS/INDICATOR/LANGUAGE/@count"/>
          </xsl:call-template>
        </xsl:variable>
        <xsl:for-each select="//REPORT/INDICATORS/INDICATOR">
          <xsl:if test="position() &gt; //REPORT/INDICATORS/PER_PAGE * (//REPORT/INDICATORS/CURRENT_PAGE - 1)">
            <xsl:if test="position() &lt;= //REPORT/INDICATORS/PER_PAGE * //REPORT/INDICATORS/CURRENT_PAGE">
              <tr>
                <xsl:if test="position() mod 2 = 1">
                  <xsl:attribute name="class">alternate</xsl:attribute>
                </xsl:if>
                <td align="center"><xsl:value-of select="position()"/>.</td>
                <td align="center" style="padding-top: 11px;padding-bottom: 0;">
                  <xsl:if test="LANGUAGE/@flag != ''">
                    <img src="{//REPORT/PATHTOCACHE}{LANGUAGE/@flag}"/>
                  </xsl:if>
                </td>
                <td>
                  <xsl:value-of select="LANGUAGE/@name"/> (<xsl:value-of select="LANGUAGE/@name_en"/>) <xsl:value-of select="LANGUAGE"/>
                </td>
                <td align="center">
                  <xsl:value-of select="LANGUAGE/@count"/>
                </td>
              </tr>
              <tr>
                <xsl:if test="position() mod 2 = 1">
                  <xsl:attribute name="class">alternate</xsl:attribute>
                </xsl:if>
                <xsl:if test="$maxUniq&gt;0">
                  <td colspan="4" style="padding: 0 8px 3px 8px;">
                    <div class="progress"><div class="percent"><xsl:value-of select='format-number(LANGUAGE/@count * 100 div sum(//REPORT/INDICATORS/INDICATOR/LANGUAGE/@count),"#.##")'/>%</div><div class="bar">
                      <xsl:attribute name="style">width:<xsl:value-of select='LANGUAGE/@count * 100 div sum(//REPORT/INDICATORS/INDICATOR/LANGUAGE/@count)'/>%</xsl:attribute>
                    </div></div>
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