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
          <th class="manage-column" style="text-align:center;"><xsl:value-of select="//REPORT/TRANSLATE/URI"/></th>
          <th class="manage-column" style="text-align:center;width:160px;"><xsl:value-of select="//REPORT/TRANSLATE/REFERRER"/></th>
          <th class="manage-column" style="text-align:center;width:170px;"><xsl:value-of select="//REPORT/TRANSLATE/VIEW"/></th>
          <th class="manage-column" style="text-align:center;width:190px;"><xsl:value-of select="//REPORT/TRANSLATE/USERROBOT"/></th>
        </tr>
      </thead>
      <tfoot>
      </tfoot>
      <tbody>
        <xsl:variable name="maxUniq">
          <xsl:call-template name="maximum">
            <xsl:with-param name="pSequence" select="//REPORT/INDICATORS/INDICATOR/COUNT"/>
          </xsl:call-template>
        </xsl:variable>
        <xsl:variable name="minUniq">
          <xsl:call-template name="minimum">
            <xsl:with-param name="pSequence" select="//REPORT/INDICATORS/INDICATOR/COUNT"/>
          </xsl:call-template>
        </xsl:variable>
        <xsl:for-each select="//REPORT/INDICATORS/INDICATOR">
          <xsl:if test="position() &gt; //REPORT/INDICATORS/PER_PAGE * (//REPORT/INDICATORS/CURRENT_PAGE - 1)">
            <xsl:if test="position() &lt;= //REPORT/INDICATORS/PER_PAGE * //REPORT/INDICATORS/CURRENT_PAGE">
              <tr>
                <td class="center"><xsl:value-of select="position()"/>.</td>
                <td>
                  <xsl:attribute name="title">
                    <xsl:value-of select="URI"/>
                  </xsl:attribute>
                  <xsl:choose>
                    <xsl:when test="URI='/'">
                      <xsl:value-of select="//REPORT/TRANSLATE/MAINPAGE"/>
                    </xsl:when>
                    <xsl:otherwise>
                      <xsl:value-of select="URI"/>
                    </xsl:otherwise>
                  </xsl:choose>
                </td>
                <td class="center">
                  <a onclick="jQuery('#extend{position()}').toggle();return false;" href="" class="btn btn-small"><xsl:value-of select="count(REFERRER)"/> »</a>
                </td>
                <td class="center">
                  <xsl:value-of select="COUNT"/>
                </td>
                <td class="center">
                  <xsl:value-of select="USER"/>/<xsl:value-of select="ROBOT"/>
                </td>
              </tr>
              <tr></tr>
              <tr id="extend{position()}" style="display:none;">
                <td colspan="5">
                  <xsl:for-each select="REFERRER">
                    <div style="margin-left:100px;border-bottom: 1px solid #CCC;"><xsl:value-of select="position()"/>. <a href="{.}" target="_blank">
                    <xsl:choose>
                      <xsl:when test=".='/'">
                        <xsl:value-of select="//REPORT/TRANSLATE/MAINPAGE"/>
                      </xsl:when>
                      <xsl:otherwise>
                        <xsl:value-of select="."/>
                      </xsl:otherwise>
                    </xsl:choose>
                    </a></div>
                  </xsl:for-each>
                </td>
              </tr>
              <tr></tr>
              <tr>
                <xsl:if test="$maxUniq&gt;0">
                  <td colspan="5" style="padding: 0px;">
                    <div class="progress">
                      <div class="bar">
                        <xsl:attribute name="style">width:<xsl:value-of select='COUNT * 100 div sum(//REPORT/INDICATORS/INDICATOR/COUNT)'/>%</xsl:attribute>
                        <xsl:value-of select='format-number(COUNT * 100 div sum(//REPORT/INDICATORS/INDICATOR/COUNT),"#.##")'/>%
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