<?/*A "quick" microbenchmark update PHP 5.4*/?>

<?$this->followUpTo('a-quick-microbenchmark')?>

<p>With the <a href="http://php.net/releases/5_4_0.php">release of PHP 5.4</a> being toted as a "significant performance improvement" I thought it might be fun to go back and run the quicksort benchmark against the new release.  I upgraded to 5.4.3 on my local machine and ran the tests.</p>

<h2>Results</h2>

<p><table class="bordered">
   <tr><th></th><th>Language</th><th>Version</th><th>Time in milliseconds</th></tr>
   <tr><td>1</td><td>C++</td><td>MS 16.00.30319.01 for 80x86</td><td>75</td></tr>
   <tr><td>2</td><td>C# Array.Sort()</td><td>.NET 3.5</td><td>100</td></tr>
   <tr><td>3</td><td>Java</td><td>1.6.0_23</td><td>105</td></tr>
   <tr><td>4</td><td>Groovy api (aka java)</td><td>1.8.5</td><td>110</td></tr>
   <tr><td>5</td><td>Java Arrays.sort()</td><td>1.6.0_23</td><td>121</td></tr>
   <tr><td>6</td><td>C#</td><td>.NET 3.5</td><td>126</td></tr>
   <tr><td>7</td><td>Scala</td><td>2.9.1.final</td><td>128</td></tr>
   <tr><td>8</td><td>Node.js (v8)</td><td>0.6.7</td><td>175</td></tr>
   <tr><td>9</td><td>Chrome JavaScript</td><td>16.0.912.77</td><td>182</td></tr>
   <tr><td>10</td><td>Ruby api array.sort!</td><td>1.9.2p290</td><td>250</td></tr>
   <tr><td>11</td><td>Firefox JavaScript</td><td>10.0</td><td>271</td></tr>
   <tr><td>12</td><td>IE JavaScript</td><td>9.0.8112.16421</td><td>307</td></tr>
   <tr><td>13</td><td>IE JavaScript api sort()</td><td>9.0.8112.16421</td><td>375</td></tr>
   <tr><td>14</td><td>Node.js (v8) api sort()</td><td>0.6.7</td><td>480</td></tr>
   <tr><td>15</td><td>Chrome JavaScript api sort()</td><td>16.0.912.77</td><td>520</td></tr>
   <tr><td>16</td><td>Python api sort()</td><td>3.1.3</td><td>814</td></tr>
   <tr><td>17</td><td>PHP api sort()</td><td>5.3.8</td><td>1441</td></tr>
   <tr><td>18</td><td>PHP api sort()</td><td>5.4.3</td><td>1516</td></tr>
   <tr><td>19</td><td>Firefox JavaScript api sort()</td><td>10.0</td><td>3490</td></tr>
   <tr><td>20</td><td>Ruby</td><td>1.9.2p290</td><td>3520</td></tr>
   <tr><td>21</td><td>Groovy</td><td>1.8.5</td><td>4100</td></tr>
   <tr><td>22</td><td>PHP</td><td>5.4.3</td><td>5302</td></tr>
   <tr><td>23</td><td>Python</td><td>3.1.3</td><td>8100</td></tr>
   <tr><td>24</td><td>PHP</td><td>5.3.8</td><td>9700</td></tr>
</table></p>

<h2>Performance improvement? Yes!</h2>

<p>The PHP api version was the same as expected.  I didn't look at the source but I would not have expected the sort algorthim to have been changed.  As you can see though, the implemented version was "significantly" faster as promised... a whopping 45% faster!  That is quite the improvement indeed.  There are some other breaking compatibility changes though to be aware of.  Anyone still relying on <code>register_globals</code> or <code>magic_quotes</code> please step forward !!</p>

<p>As before you can view the code for each implementation on <a href="https://github.com/briannesbitt/QuicksortMicrobenchmark">github</a>.</p>