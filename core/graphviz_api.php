<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Graphviz API
 *
 * Wrapper classes around Graphviz utilities (dot and neato) for
 * directed and undirected graph generation. These wrappers are enhanced
 * enough just to support relationship_graph_api.php. They don't
 * support subgraphs yet.
 *
 * The original Graphviz package including documentation is available at:
 * 	- https://www.graphviz.org/
 *
 * @package CoreAPI
 * @subpackage GraphvizAPI
 * @author Juliano Ravasi Ferraz <jferraz at users sourceforge net>
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses constant_inc.php
 * @uses utility_api.php
 */

use Mantis\Exceptions\ServiceException;
use Mantis\Exceptions\StateException;

require_api( 'constant_inc.php' );
require_api( 'utility_api.php' );

# constant(s) defining the output formats supported by dot and neato.
define( 'GRAPHVIZ_ATTRIBUTED_DOT', 0 );
define( 'GRAPHVIZ_PS', 1 );
define( 'GRAPHVIZ_HPGL', 2 );
define( 'GRAPHVIZ_PCL', 3 );
define( 'GRAPHVIZ_MIF', 4 );
define( 'GRAPHVIZ_PLAIN', 6 );
define( 'GRAPHVIZ_PLAIN_EXT', 7 );
define( 'GRAPHVIZ_GIF', 11 );
define( 'GRAPHVIZ_JPEG', 12 );
define( 'GRAPHVIZ_PNG', 13 );
define( 'GRAPHVIZ_WBMP', 14 );
define( 'GRAPHVIZ_XBM', 15 );
define( 'GRAPHVIZ_ISMAP', 16 );
define( 'GRAPHVIZ_IMAP', 17 );
define( 'GRAPHVIZ_CMAP', 18 );
define( 'GRAPHVIZ_CMAPX', 19 );
define( 'GRAPHVIZ_VRML', 20 );
define( 'GRAPHVIZ_SVG', 25 );
define( 'GRAPHVIZ_SVGZ', 26 );
define( 'GRAPHVIZ_CANONICAL_DOT', 27 );
define( 'GRAPHVIZ_PDF', 28 );

/**
 * Base class for graph creation and manipulation. By default,
 * undirected graphs are generated. For directed graphs, use Digraph
 * class.
 */
class Graph {

	/**
	 * Graphviz tools.
	 * List is limited to the tools used in MantisBT; refer to documentation
	 * for other possible values.
	 */
	const TOOL_DOT = 'dot';
	const TOOL_NEATO = 'neato';
	const TOOL_CIRCO = 'circo';

	/**
	 * Name
	 */
	public $name = 'G';

	/**
	 * Attributes
	 */
	public $attributes = array();

	/**
	 * Default node
	 */
	public $default_node = null;

	/**
	 * Default edge
	 */
	public $default_edge = null;

	/**
	 * Nodes
	 */
	public $nodes = array();

	/**
	 * Edges
	 */
	public $edges = array();

	/**
	 * Graphviz tool
	 */
	public $graphviz_tool;

	/**
	 * Formats
	 */
	public $formats = array(
		'dot' => array(
			'binary' => false,
			'type' => GRAPHVIZ_ATTRIBUTED_DOT,
			'mime' => 'text/x-graphviz',
		),
		'ps' => array(
			'binary' => false,
			'type' => GRAPHVIZ_PS,
			'mime' => 'application/postscript',
		),
		'hpgl' => array(
			'binary' => true,
			'type' => GRAPHVIZ_HPGL,
			'mime' => 'application/vnd.hp-HPGL',
		),
		'pcl' => array(
			'binary' => true,
			'type' => GRAPHVIZ_PCL,
			'mime' => 'application/vnd.hp-PCL',
		),
		'mif' => array(
			'binary' => true,
			'type' => GRAPHVIZ_MIF,
			'mime' => 'application/vnd.mif',
		),
		'gif' => array(
			'binary' => true,
			'type' => GRAPHVIZ_GIF,
			'mime' => 'image/gif',
		),
		'jpg' => array(
			'binary' => false,
			'type' => GRAPHVIZ_JPEG,
			'mime' => 'image/jpeg',
		),
		'jpeg' => array(
			'binary' => true,
			'type' => GRAPHVIZ_JPEG,
			'mime' => 'image/jpeg',
		),
		'png' => array(
			'binary' => true,
			'type' => GRAPHVIZ_PNG,
			'mime' => 'image/png',
		),
		'wbmp' => array(
			'binary' => true,
			'type' => GRAPHVIZ_WBMP,
			'mime' => 'image/vnd.wap.wbmp',
		),
		'xbm' => array(
			'binary' => false,
			'type' => GRAPHVIZ_XBM,
			'mime' => 'image/x-xbitmap',
		),
		'ismap' => array(
			'binary' => false,
			'type' => GRAPHVIZ_ISMAP,
			'mime' => 'text/plain',
		),
		'imap' => array(
			'binary' => false,
			'type' => GRAPHVIZ_IMAP,
			'mime' => 'application/x-httpd-imap',
		),
		'cmap' => array(
			'binary' => false,
			'type' => GRAPHVIZ_CMAP,
			'mime' => 'text/html',
		),
		'cmapx' => array(
			'binary' => false,
			'type' => GRAPHVIZ_CMAPX,
			'mime' => 'application/xhtml+xml',
		),
		'vrml' => array(
			'binary' => true,
			'type' => GRAPHVIZ_VRML,
			'mime' => 'x-world/x-vrml',
		),
		'svg' => array(
			'binary' => false,
			'type' => GRAPHVIZ_SVG,
			'mime' => 'image/svg+xml',
		),
		'svgz' => array(
			'binary' => true,
			'type' => GRAPHVIZ_SVGZ,
			'mime' => 'image/svg+xml',
		),
		'pdf' => array(
			'binary' => true,
			'type' => GRAPHVIZ_PDF,
			'mime' => 'application/pdf',
		),
	);

	/**
	 * Constructor for Graph objects.
	 *
	 * @param string $p_name       Graph name.
	 * @param array  $p_attributes Attributes.
	 * @param string $p_tool       Graph generation tool, defaults to neato.
	 *
	 * @throws StateException   if $g_relationship_graph_path is not readable
	 * @throws ServiceException if $p_tool not found or not executable
	 */
	function __construct( $p_name = 'G', array $p_attributes = array(), $p_tool = self::TOOL_NEATO ) {
		if( is_string( $p_name ) ) {
			$this->name = $p_name;
		}

		$this->set_attributes( $p_attributes );

		# On Unix, we have symlinks for various Graphviz layouts, but on
		# Windows, only the main dot tool exists so we need to set the layout
		# engine with -K parameter.
		if( is_windows_server() ) {
			$t_opt = ' -K' . $p_tool;
			$p_tool = self::TOOL_DOT . '.exe';
		} else {
			$t_opt ='';
		}

		$t_dir = config_get( 'relationship_graph_path' );
		if( $t_dir ){
			# Make sure directory exists and is accessible
			if( !is_dir( $t_dir ) || !is_readable( $t_dir ) ) {
				throw new StateException(
					"Graphviz binaries directory '$t_dir' not found or not readable",
					ERROR_CONFIG_OPT_INVALID,
					array( 'relationship_graph_path', $t_dir )
				);
			}

			# Make sure the tool is executable
			$p_tool = $t_dir . $p_tool;
			if( !is_file( $p_tool ) || !is_executable( $p_tool ) ) {
				$t_msg = "Graphviz tool '$p_tool' not found or not executable";
				throw new ServiceException(
					$t_msg,
					ERROR_RELGRAPH_GENERATION,
					array( $t_msg )
				);
			}
		}

		$this->graphviz_tool = $p_tool . $t_opt;
	}

	/**
	 * Sets graph attributes.
	 * @param array $p_attributes Attributes.
	 * @return void
	 */
	function set_attributes( array $p_attributes ) {
		if( is_array( $p_attributes ) ) {
			$this->attributes = $p_attributes;
		}
	}

	/**
	 * Sets default attributes for all nodes of the graph.
	 * @param array $p_attributes Attributes.
	 * @return void
	 */
	function set_default_node_attr( array $p_attributes ) {
		if( is_array( $p_attributes ) ) {
			$this->default_node = $p_attributes;
		}
	}

	/**
	 * Sets default attributes for all edges of the graph.
	 * @param array $p_attributes Attributes.
	 * @return void
	 */
	 function set_default_edge_attr( array $p_attributes ) {
		if( is_array( $p_attributes ) ) {
			$this->default_edge = $p_attributes;
		}
	}

	/**
	 * Adds a node to the graph.
	 * @param string $p_name       Node name.
	 * @param array  $p_attributes Attributes.
	 * @return void
	 */
	 function add_node( $p_name, array $p_attributes = array() ) {
		if( is_array( $p_attributes ) ) {
			$this->nodes[$p_name] = $p_attributes;
		}
	}

	/**
	 * Adds an edge to the graph.
	 * @param string $p_src        Source.
	 * @param string $p_dst        Destination.
	 * @param array  $p_attributes Attributes.
	 * @return void
	 */
	 function add_edge( $p_src, $p_dst, array $p_attributes = array() ) {
		if( is_array( $p_attributes ) ) {
			$this->edges[] = array(
				'src' => $p_src,
				'dst' => $p_dst,
				'attributes' => $p_attributes,
			);
		}
	}

	/**
	 * Check if an edge is already present.
	 * @param string $p_src Source.
	 * @param string $p_dst Destination.
	 * @return boolean
	 */
	function is_edge_present( $p_src, $p_dst ) {
		foreach( $this->edges as $t_edge ) {
			if( $t_edge['src'] == $p_src && $t_edge['dst'] == $p_dst ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Generates an undirected graph representation (suitable for neato).
	 * @return string
	 */
	function generate() {
		$t_graph = 'graph ' . $this->name . ' {' . "\n";
		$t_graph .= $this->graph_defaults();

		foreach( $this->nodes as $t_name => $t_attr ) {
			$t_name = '"' . addcslashes( $t_name, "\0..\37\"\\" ) . '"';
			$t_attr = $this->build_attribute_list( $t_attr );
			$t_graph .= "\t" . $t_name . ' ' . $t_attr . ";\n";
		}

		foreach( $this->edges as $t_edge ) {
			$t_src = '"' . addcslashes( $t_edge['src'], "\0..\37\"\\" ) . '"';
			$t_dst = '"' . addcslashes( $t_edge['dst'], "\0..\37\"\\" ) . '"';
			$t_attr = $t_edge['attributes'];
			$t_attr = $this->build_attribute_list( $t_attr );
			$t_graph .= "\t" . $t_src . ' -- ' . $t_dst . ' ' . $t_attr . ";\n";
		}

		return $t_graph . "}\n";
	}

	/**
	 * Outputs a graph image or map in the specified format.
	 *
	 * @param string  $p_format  Graphviz output format.
	 * @param boolean $p_headers Whether to sent http headers.
	 * @return void
	 *
	 * @throws ServiceException if Graphviz execution fails
	 */
	function output( $p_format = 'dot', $p_headers = false ) {
		# Check if it is a recognized format.
		if( !isset( $this->formats[$p_format] ) ) {
			trigger_error( ERROR_GENERIC, ERROR );
		}

		# Start dot process

		# Use a temp file to capture stderr output. A pipe won't work due to a
		# limitation on Windows which does not support non-blocking streams and
		# hangs if there is no error output (stream_set_blocking has no effect).
		$t_err_file = tempnam( sys_get_temp_dir(), 'mantis_relgraph_err.' );

		$t_command = escapeshellcmd( $this->graphviz_tool . ' -T' . $p_format );
		$t_descriptors = array(
			0 => array( 'pipe', 'r', ),
			1 => array( 'pipe', 'w', ),
			2 => array( 'file', $t_err_file, 'w', ),
			);
		$t_pipes = array();
		$t_process = proc_open( $t_command, $t_descriptors, $t_pipes );

		if( $t_process === false ) {
			$t_msg = "proc_open() call failed";
			throw new ServiceException(
				$t_msg,
				ERROR_RELGRAPH_GENERATION,
				array( $t_msg )
			);
		}

		# Filter generated output through dot
		fwrite( $t_pipes[0], $this->generate() );
		fclose( $t_pipes[0] );

		# Read output
		$t_stdout = '';
		while( !feof( $t_pipes[1] ) ) {
			$t_stdout .= fgets( $t_pipes[1], 1024 );
		}
		fclose( $t_pipes[1] );
		fclose( $t_pipes[2] );
		proc_close( $t_process );

		# Check for errors
		$t_errors = file_get_contents( $t_err_file );
		unlink( $t_err_file );
		if( $t_errors === false || $t_errors ) {
			$t_errors = rtrim( $t_errors );
			throw new ServiceException(
				$t_errors,
				ERROR_RELGRAPH_GENERATION,
				array( $t_errors )
			);
		}

		if( $p_headers ) {
			header( 'Content-Type: ' . $this->formats[$p_format]['mime'] );
			header( 'Content-Length: ' . strlen( $t_stdout ) );
		}
		echo $t_stdout;
	}

	/**
	 * Build a node or edge attribute list.
	 *
	 * @param array $p_attributes Attributes.
	 *
	 * @return string
	 */
	protected function build_attribute_list( array $p_attributes ) {
		if( empty( $p_attributes ) ) {
			return '';
		}

		$t_result = array();

		foreach( $p_attributes as $t_name => $t_value ) {
			if( !preg_match( '/[a-zA-Z]+/', $t_name ) ) {
				continue;
			}

			if( is_string( $t_value ) ) {
				$t_value = '"' . addcslashes( $t_value, "\0..\37\"\\" ) . '"';
			} else if( is_integer( $t_value ) or is_float( $t_value ) ) {
				$t_value = (string)$t_value;
			} else {
				continue;
			}

			$t_result[] = $t_name . '=' . $t_value;
		}

		return '[ ' . implode( ', ', $t_result ) . ' ]';
	}

	/**
	 * Generate default graph attributes.
	 *
	 * @return string
	 */
	protected function graph_defaults() {
		$t_defaults = '';

		foreach( $this->attributes as $t_name => $t_value ) {
			if( !preg_match( '/[a-zA-Z]+/', $t_name ) ) {
				continue;
			}

			if( is_string( $t_value ) ) {
				$t_value = '"' . addcslashes( $t_value, "\0..\37\"\\" ) . '"';
			} else if( is_integer( $t_value ) or is_float( $t_value ) ) {
				$t_value = (string)$t_value;
			} else {
				continue;
			}

			$t_defaults .= "\t" . $t_name . '=' . $t_value . ";\n";
		}

		if( null !== $this->default_node ) {
			$t_attr = $this->build_attribute_list( $this->default_node );
			$t_defaults .= "\t" . 'node ' . $t_attr . ";\n";
		}

		if( null !== $this->default_edge ) {
			$t_attr = $this->build_attribute_list( $this->default_edge );
			$t_defaults .= "\t" . 'edge ' . $t_attr . ";\n";
		}

		return $t_defaults;
	}
}

/**
 * Directed graph creation and manipulation.
 */
class Digraph extends Graph {

	/**
	 * Constructor for Digraph objects.
	 *
	 * @param string $p_name       Name of the graph.
	 * @param array  $p_attributes Attributes.
	 * @param string $p_tool       Graph generation tool, defaults to dot.
	 *
	 * @throws StateException   if $g_relationship_graph_path is not readable
	 * @throws ServiceException if $p_tool not found or not executable
	 */
	function __construct( $p_name = 'G', array $p_attributes = array(), $p_tool = self::TOOL_DOT ) {
		parent::__construct( $p_name, $p_attributes, $p_tool );
	}

	/**
	 * Generates a directed graph representation (suitable for dot).
	 * @return string
	 */
	function generate() {
		$t_graph = 'digraph ' . $this->name . ' {' . "\n";
		$t_graph .= $this->graph_defaults();

		foreach( $this->nodes as $t_name => $t_attr ) {
			$t_name = '"' . addcslashes( $t_name, "\0..\37\"\\" ) . '"';
			$t_attr = $this->build_attribute_list( $t_attr );
			$t_graph .= "\t" . $t_name . ' ' . $t_attr . ";\n";
		}

		foreach( $this->edges as $t_edge ) {
			$t_src = '"' . addcslashes( $t_edge['src'], "\0..\37\"\\" ) . '"';
			$t_dst = '"' . addcslashes( $t_edge['dst'], "\0..\37\"\\" ) . '"';
			$t_attr = $t_edge['attributes'];
			$t_attr = $this->build_attribute_list( $t_attr );
			$t_graph .= "\t" . $t_src . ' -> ' . $t_dst . ' ' . $t_attr . ";\n";
		}

		return $t_graph . "}\n";
	}
}
