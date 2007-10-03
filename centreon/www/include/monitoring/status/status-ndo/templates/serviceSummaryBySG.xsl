<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE toto[
  <!ENTITY nbsp "&#160;" >
]>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:template match="/">


	<xsl:for-each select="//sg">

<table id="ListTable">
	<tr class='list_lvl_1'>

			<xsl:if test="//i/s = 1">
	<td colspan="4">
		<xsl:value-of select="sgn"/>
	</td>
			</xsl:if>
			<xsl:if test="//i/s = 0">
	<td colspan="3">
		<xsl:value-of select="sgn"/>
	</td>
			</xsl:if>

	</tr>
	<tr class='ListHeader'>
		<td colspan="2"  class="ListColHeaderCenter" style="white-space:nowrap;" id="host_name"  width="200"></td>

			<xsl:if test="//i/s = 1">
				<td class="ListColHeaderCenter" style="white-space:nowrap;" id="host_state" width="40">Status</td>
			</xsl:if>

		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="services"></td>
	</tr>

	<xsl:for-each select="h">
	<tr>
		<xsl:attribute name="id">trStatus</xsl:attribute>
  		<xsl:attribute name="class"><xsl:value-of select="@class" /></xsl:attribute>

				<td class="ListColLeft"  width="160">
					<xsl:element name="a">
					  	<xsl:attribute name="href">oreon.php?p=201&amp;o=hd&amp;host_name=<xsl:value-of select="hn"/></xsl:attribute>
						<xsl:attribute name="class">pop</xsl:attribute>
						<xsl:value-of select="hn"/>
					</xsl:element>
				</td>

				<td class="ListColLeft">
					<xsl:element name="a">
					  	<xsl:attribute name="href">oreon.php?o=svc&amp;p=20201&amp;host_name=<xsl:value-of select="hn"/></xsl:attribute>
							<xsl:element name="img">
							  	<xsl:attribute name="src">./img/icones/16x16/view.gif</xsl:attribute>
							</xsl:element>
					</xsl:element>
					<xsl:element name="a">
					  	<xsl:attribute name="href">oreon.php?p=40210&amp;host_name=<xsl:value-of select="hn"/></xsl:attribute>
							<xsl:element name="img">
							  	<xsl:attribute name="src">./img/icones/16x16/column-chart.gif</xsl:attribute>
							</xsl:element>
					</xsl:element>
				</td>

			<xsl:if test="//i/s = 1">
				<td class="ListColLeft">
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="hc"/>;
    						</xsl:attribute>
						<xsl:value-of select="hs"/>
				</td>
			</xsl:if>

				<td class="ListColLeft">

					<xsl:if test="sk >= 1">
						<span>
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="sk/@color" />
    						</xsl:attribute>
							<xsl:value-of select="sk"/>OK
						</span>&nbsp;
					</xsl:if>


					<xsl:if test="sw >= 1">
						<span>
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="sw/@color" />
    						</xsl:attribute>
						<xsl:value-of select="sw"/>WARNING
						</span>&nbsp;
					</xsl:if>
					<xsl:if test="sc >= 1">
						<span>
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="sc/@color" />
    						</xsl:attribute>
						<xsl:value-of select="sc"/>CRITICAL
						</span>&nbsp;
					</xsl:if>
					<xsl:if test="su >= 1">
						<span>
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="su/@color" />
    						</xsl:attribute>
						<xsl:value-of select="su"/>CRITICAL
						</span>&nbsp;
					</xsl:if>
					<xsl:if test="sp >= 1">
						<span>
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="sp/@color" />
    						</xsl:attribute>
						<xsl:value-of select="sp"/>PENDING
						</span>
					</xsl:if>

				</td>
	</tr>
</xsl:for-each>
</table>
<br/>
</xsl:for-each>

</xsl:template>
</xsl:stylesheet>