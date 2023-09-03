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
 * A webservice interface to Mantis Bug Tracker
 *
 * @package   MantisBT
 * @copyright Copyright 2017-2023 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link      https://mantisbt.org
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * A middleware class that disables caching.
 */
class CacheMiddleware implements MiddlewareInterface
{

	public function process( Request $request, RequestHandler $handler ): ResponseInterface {
		return $handler->handle( $request )->
			withoutHeader( 'Cache-Control' )->
			withHeader( 'Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0' );
	}

}
