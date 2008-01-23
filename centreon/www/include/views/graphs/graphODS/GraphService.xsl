<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="root">


<xsl:if test="host">

<!-- 
<div id="div1" class="cachediv" style="padding-bottom:10px;">  
-->

<!-- 
	    <div id="div1" style="padding-bottom:10px;">
		<form name="formu">
    	    <table id="ListTable">
                <tr class="ListHeader">
                	<td class="FormHeader" ><xsl:value-of select="//lang/optionAdvanced"/></td>
                </tr>
               <tr class="list_one">
               		<td >
               		<xsl:value-of select="//lang/period"/>
						<xsl:text> </xsl:text> 
               		
               		</td>
               </tr>

				<tr class="list_lvl_1">
					<td><xsl:value-of select="//lang/start"/>
						<xsl:text> </xsl:text> 
						<input id="StartDate" name="StartDate" type="text" value="" onclick="displayDatePicker('StartDate', this)" size="8" />
						<xsl:text> </xsl:text> 
						<input id="StartTime" name="StartTime" type="text" value="" onclick="displayTimePicker('StartTime', this)" size="4" />  
						<xsl:text> </xsl:text> 
						<xsl:value-of select="//lang/end"/>
						<xsl:text> </xsl:text> 
						<input id="EndDate" name="EndDate" type="text" value="" onclick="displayDatePicker('EndDate', this)" size="8" />
						<xsl:text> </xsl:text> 
						<input id="EndTime" name="EndTime" type="text" value="" onclick="displayTimePicker('EndTime', this)" size="4" />  
					</td>
				</tr>
        	</table>
		</form>
    	</div>
		<div valign="top" align='center'>
			<input onclick="DisplayHidden('div1');" name="advanced" value="Options" type="button" />
			<xsl:text> </xsl:text> 
			<xsl:element name="input">
				<xsl:attribute name="type">button</xsl:attribute>
				<xsl:attribute name="name">graph</xsl:attribute>
				<xsl:attribute name="value">graph</xsl:attribute>
				<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//opid"/>', this.form); return false;</xsl:attribute>
			</xsl:element>
		</div>
<br/>
-->
		<div style="position:relative; z-index: 10; left:0px; top: -50px"  valign="top" align='center'>
			<xsl:element name="input">
				<xsl:attribute name="type">button</xsl:attribute>
				<xsl:attribute name="name">graph</xsl:attribute>
				<xsl:attribute name="value">graph</xsl:attribute>
				<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//opid"/>', this.form); return false;</xsl:attribute>
			</xsl:element>
		</div>
<br/>

	<div>
		<table id="ListTable">
	        <tr class="ListHeader">
	        	<td class="FormHeader" colspan="2"><img src='./img/icones/16x16/column-chart.gif'/>Host : </td>
	        </tr>
			<xsl:for-each select="//svc">
		        <tr class="list_one">
					<td class='ListColLeft' valign="top" align='center'> <b>Service : <xsl:value-of select="name"/></b></td>
	
					<td style="text-align:right;width:42px;">
	
					<a href="">
						<img src="./img/icones/16x16/text_binary_csv.gif"/>
					</a>
	
					<a href=''>
						<img src="./img/icones/16x16/text_binary_xml.gif"/>
					</a>
					</td>
				</tr>
				<tr>
	    			<td class='ListColCenter' valign="top" align='center'>
				    	<div id="imggraph">


			<xsl:if test="split = 0">

							<xsl:element name="a">
							<xsl:attribute name="onClick">graph_4_host('HS_<xsl:value-of select="service_id"/>', ''); return false;</xsl:attribute>
							<xsl:attribute name="href">#</xsl:attribute>

									<xsl:element name="img">
									  	<xsl:attribute name="src">
	./include/views/graphs/graphODS/generateImages/generateODSImage.php?session_id=<xsl:value-of select="//sid"/>&amp;index=<xsl:value-of select="index"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/>
									  	</xsl:attribute>
									</xsl:element>
							</xsl:element>
			</xsl:if>


			<xsl:if test="split = 1">
				<xsl:for-each select="//metric">
								<xsl:element name="a">
								<xsl:attribute name="onClick">graph_4_host('HS_<xsl:value-of select="//service_id"/>', ''); return false;</xsl:attribute>
								<xsl:attribute name="href">#</xsl:attribute>
	
										<xsl:element name="img">
										  	<xsl:attribute name="src">
		./include/views/graphs/graphODS/generateImages/generateODSMetricImage.php?session_id=<xsl:value-of select="//sid"/>&amp;cpt=1&amp;metric=<xsl:value-of select="metric_id"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/>
										  	</xsl:attribute>
										</xsl:element>
								</xsl:element>
								<br/>
				</xsl:for-each>


			</xsl:if>


						 <br/>
						 </div> 
					</td>
				</tr>			
				
			</xsl:for-each>
	    </table>
	</div>
</xsl:if>

<xsl:if test="svc">
		<div style="position:relative; z-index: 10; left:0px; top: -50px"  valign="top" align='center'>
			<xsl:element name="input">
				<xsl:attribute name="type">button</xsl:attribute>
				<xsl:attribute name="name">graph</xsl:attribute>
				<xsl:attribute name="value">graph</xsl:attribute>
				<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//opid"/>', this.form); return false;</xsl:attribute>
			</xsl:element>
		</div>
<br/>
	<div>
		<table id="ListTable">
	        <tr class="ListHeader">
	        	<td class="FormHeader" colspan="2"><img src='./img/icones/16x16/column-chart.gif'/>Service : </td>
	        </tr>
			<xsl:for-each select="//period">
		        <tr class="list_one">
					<td class='ListColLeft' valign="top" align='center'> <xsl:value-of select="name"/></td>
	
					<td style="text-align:right;width:42px;">
	
					<a href="">
						<img src="./img/icones/16x16/text_binary_csv.gif"/>
					</a>
	
					<a href=''>
						<img src="./img/icones/16x16/text_binary_xml.gif"/>
					</a>
					</td>
				</tr>
				<tr>
	    			<td class='ListColCenter' valign="top" align='center'>
				    	<div id="imggraph">




			<xsl:if test="//split = 0">
							<xsl:element name="a">
							<xsl:attribute name="onClick">graph_4_host('SS_<xsl:value-of select="//id"/>', ''); return false;</xsl:attribute>
							<xsl:attribute name="href">#</xsl:attribute>

									<xsl:element name="img">
									  	<xsl:attribute name="src">
	./include/views/graphs/graphODS/generateImages/generateODSImage.php?session_id=<xsl:value-of select="//sid"/>&amp;index=<xsl:value-of select="//index"/>&amp;end=<xsl:value-of select="end"/>&amp;start=<xsl:value-of select="start"/>
									  	</xsl:attribute>
									</xsl:element>

							</xsl:element>
			</xsl:if>



			<xsl:if test="//split = 1">
				<xsl:for-each select="metric">
								<xsl:element name="a">
							<xsl:attribute name="onClick">graph_4_host('SS_<xsl:value-of select="//id"/>', ''); return false;</xsl:attribute>
								<xsl:attribute name="href">#</xsl:attribute>
	
										<xsl:element name="img">
										  	<xsl:attribute name="src">
		./include/views/graphs/graphODS/generateImages/generateODSMetricImage.php?session_id=<xsl:value-of select="//sid"/>&amp;cpt=1&amp;metric=<xsl:value-of select="metric_id"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/>
										  	</xsl:attribute>
										</xsl:element>
								</xsl:element>
								<br/>
				</xsl:for-each>
			</xsl:if>




						 <br/>
						 </div> 
					</td>
				</tr>			
				
			</xsl:for-each>
	    </table>
	</div>

</xsl:if>

<xsl:if test="svc_zoom">


			<div style="position:relative; width:150px; left:268px; top: -50px; * html left:300px;"  valign="top" align='left'>
			<xsl:element name="input">
				<xsl:attribute name="type">button</xsl:attribute>
				<xsl:attribute name="name">graph</xsl:attribute>
				<xsl:attribute name="value">grapher</xsl:attribute>
				<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//opid"/>', this.form); return false;</xsl:attribute>
			</xsl:element>
			<xsl:text> </xsl:text>

		</div>
	    <div id="div2" style=" width:400px; position:relative;  left:325px; top: -87px"  valign="top" align='left'>

		<form name="formu2">

    	    <table id="ListTableSmall" >
                <tr class="ListHeader">
                	<td class="FormHeader" colspan="2"><xsl:value-of select="//lang/advanced"/></td>
                </tr>
				<tr>
				<td style="width:200px;">
					<table style="">
						<tr >
		               		<td>
								<xsl:value-of select="//lang/giv_gg_tpl"/>
		               		</td>
		               		<td>
		               			<xsl:element name="select">
									<xsl:attribute name="name">template_select</xsl:attribute>
									<xsl:attribute name="onChange">graph_4_host('<xsl:value-of select="//opid"/>', this.form); return false;</xsl:attribute>
		
									<xsl:for-each select="//tpl">
										<xsl:element name='option'>
											<xsl:attribute name="value"><xsl:value-of select="tpl_id"/></xsl:attribute>
		
											<xsl:if test="//tpl = tpl_id">
												<xsl:attribute name="selected">selected</xsl:attribute>
											</xsl:if>
		
											<xsl:value-of select="tpl_name"/>
										</xsl:element>
									</xsl:for-each>
		
								</xsl:element>
		               		</td>
		               	</tr>
		               	<tr >
		               		<td>
							 <xsl:value-of select="//lang/giv_split_component"/>
		               		</td>
		               		<td>
										<xsl:element name='input'>
											<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//opid"/>', this.form); return false;</xsl:attribute>
											<xsl:attribute name="name">split</xsl:attribute>
											<xsl:attribute name="type">checkbox</xsl:attribute>
		
											<xsl:if test="//split = 1">
												<xsl:attribute name="checked">checked</xsl:attribute>
											</xsl:if>
										</xsl:element>
		               		</td>
		               	</tr>
					</table>

				</td>
				<td style="border-left:0.5px solid gray;">
					<table >
		               	<tr>
						<xsl:for-each select="//metrics">
		               		<td style="padding-left:10px;">
								<xsl:element name='input'>
									<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//opid"/>', this.form); return false;</xsl:attribute>
									<xsl:attribute name="type">checkbox</xsl:attribute>
									<xsl:attribute name="name">metric</xsl:attribute>
									<xsl:attribute name="value"><xsl:value-of select="metric_id"/></xsl:attribute>
									<xsl:if test="select = 1">
										<xsl:attribute name="checked">checked</xsl:attribute>
									</xsl:if>
								</xsl:element>
		               			<xsl:value-of select="metric_name"/>
		               		</td>
						</xsl:for-each>					
		               	</tr>
					</table>
				</td>
				</tr>





        	</table>
		</form>
    	</div>

	<div style="position:relative; top: -80px;">
		<table id="ListTable">
	        <tr class="ListHeader">
	        	<td class="FormHeader" colspan="2"><img src='./img/icones/16x16/column-chart.gif'/>Service : </td>
	        </tr>
		        <tr class="list_one">
					<td class='ListColLeft' valign="top" align='center'> <xsl:value-of select="name"/></td>
	
					<td style="text-align:right;width:42px;">
	
					<a href="">
						<img src="./img/icones/16x16/text_binary_csv.gif"/>
					</a>
	
					<a href=''>
						<img src="./img/icones/16x16/text_binary_xml.gif"/>
					</a>
					</td>
				</tr>
				<tr>
	    			<td class='ListColCenter' valign="top" align='center'>
				    	<div id="imggraph">


				<xsl:if test="//split = 0">

									<xsl:element name="img">
									  	<xsl:attribute name="src">

	./include/views/graphs/graphODS/generateImages/generateODSImageZoom.php?template_id=<xsl:value-of select="//tpl"/>&amp;session_id=<xsl:value-of select="//sid"/>&amp;<xsl:value-of select="//metricsTab"/>&amp;index=<xsl:value-of select="//index"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/>



									  	</xsl:attribute>
									</xsl:element>
				</xsl:if>


				<xsl:if test="//split = 1">
					<xsl:for-each select="//metrics">
						<xsl:if test="select = 1">
							<xsl:element name="img">
							  	<xsl:attribute name="src">
			./include/views/graphs/graphODS/generateImages/generateODSMetricImage.php?template_id=<xsl:value-of select="//tpl"/>&amp;session_id=<xsl:value-of select="//sid"/>&amp;cpt=1&amp;metric=<xsl:value-of select="metric_id"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/>
							  	</xsl:attribute>
							</xsl:element>
							<br/>
						</xsl:if>
					</xsl:for-each>
				</xsl:if>

						 <br/>
						 </div>
					</td>
				</tr>
	    </table>
	</div>
</xsl:if>






</xsl:template>
</xsl:stylesheet>