<?php
/**
 * Webwork
 * Copyright (C) 2011 IceFlame.net
 *
 * Permission to use, copy, modify, and/or distribute this software for
 * any purpose with or without fee is hereby granted, provided that the
 * above copyright notice and this permission notice appear in all copies.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE
 * FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY
 * DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER
 * IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING
 * OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 *
 * @package     Webwork
 * @version     0.1-dev
 * @link        http://www.iceflame.net
 * @license     ISC License (http://www.opensource.org/licenses/ISC)
 */

/**
 * Class for managing user groups
 *
 * @author Christian Neff <christian.neff@gmail.com>
 */
class UserGroup {
    
    /**
     * The ID of the user group
     * @var      int
     * @access   private
     */
    private $_groupID = 0;
    
    /**
     * The name of the user group
     * @var      string
     * @access   private
     */
    private $_groupName = '';
    
    /**
     * Constructor
     * @param    int      $groupID   The ID of the user group
     * @return   void
     * @access   public
     */
    public function __construct($groupID) {
        global $db;

        // try to fetch user group data for further usage
        $sql = 'SELECT * FROM @PREFIX@usergroups WHERE id = {0} LIMIT 1';
        $result = $db->query($sql, array($groupID));
        if ($result->numRows() == 1) {
            $data = $result->fetchAssoc();
            $this->_groupID = $data['id'];
            $this->_groupName = $data['name'];
        } else {
            throw new Exception('User group '.$groupID.' does not exist');
        }
    }
    
    /**
     * Returns the name of the group
     * @return   string
     * @access   public
     */
    public function getName() {
        return $this->_groupName;
    }

}
