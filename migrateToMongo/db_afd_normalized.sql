-- ----------------------------
-- Procedure structure for comparison_A
-- ----------------------------
CREATE DEFINER=`root`@`localhost` PROCEDURE `comparison_A`()
BEGIN
	Select 	policy.parentPolicyID,
					policyID, 
					comment_policy.commentID_auto, 
					`comment`.AFDID,
					afd_category.afd_catLabel
	from policy
	INNER JOIN comment_policy
  on  policy.policyID = comment_policy.comment_policyLabel
	INNER JOIN `comment`
	on comment_policy.commentID_auto = `comment`.commentID_auto 
	INNER JOIN afd_category
  on `comment`.AFDID = afd_category.AfDID;
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for comparison_B
-- ----------------------------
CREATE DEFINER=`root`@`localhost` PROCEDURE `comparison_B`()
BEGIN
	Select 	policy.parentPolicyID,
					policyID, 
					comment_policy.commentID_auto, 
					`comment`.AFDID,
					afd_category.afd_catLabel
	from policy
	INNER JOIN comment_policy
  on  policy.policyID = comment_policy.comment_policyLabel
	INNER JOIN `comment`
	on comment_policy.commentID_auto = `comment`.commentID_auto 
	INNER JOIN afd_category
  on `comment`.AFDID = afd_category.AfDID ;
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for comparison_C
-- ----------------------------
CREATE DEFINER=`root`@`localhost` PROCEDURE `comparison_C`()
BEGIN
	Select 	policy.parentPolicyID, 
					policyID, 
					comment_policy.commentID_auto, 
					`comment`.AFDID,
					afd_category.afd_catLabel,
					parentCategoryLabel
	from policy
	INNER JOIN comment_policy
  on  policy.policyID = comment_policy.comment_policyLabel
	INNER JOIN `comment`
	on comment_policy.commentID_auto = `comment`.commentID_auto 
	INNER JOIN afd_category
  on `comment`.AFDID = afd_category.AfDID  
	INNER JOIN category
	on  afd_category.afd_catLabel = category.categoryLabel;
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for comparison_D
-- ----------------------------
CREATE DEFINER=`root`@`localhost` PROCEDURE `comparison_D`()
BEGIN
	Select 	parentpolicy.policyFamily,
					policy.parentPolicyID, 
					policyID, 
					comment_policy.commentID_auto, 
					`comment`.AFDID,
					afd_category.afd_catLabel,
					category.parentCategoryLabel,
					parentcategory.categoryFamily
	from policy
	INNER JOIN parentpolicy
  on  policy.parentPolicyID = parentpolicy.parentPolicyID
	INNER JOIN comment_policy
  on  policy.policyID = comment_policy.comment_policyLabel
	INNER JOIN `comment`
	on comment_policy.commentID_auto = `comment`.commentID_auto 
	INNER JOIN afd_category
  on `comment`.AFDID = afd_category.AfDID  
	INNER JOIN category
	on  afd_category.afd_catLabel = category.categoryLabel
	INNER JOIN parentcategory
	on  category.parentCategoryLabel = parentcategory.parentCategoryLabel;
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for comparison_E
-- ----------------------------
CREATE DEFINER=`root`@`localhost` PROCEDURE `comparison_E`()
BEGIN
	Select 	policy.parentPolicyID, 
					policyID, 
					comment_policy.commentID_auto
	from policy
	INNER JOIN comment_policy
  on  policy.policyID = comment_policy.comment_policyLabel;
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for comparison_F
-- ----------------------------
CREATE DEFINER=`root`@`localhost` PROCEDURE `comparison_F`()
BEGIN
	Select 	policy.parentPolicyID, 
					policyID, 
					comment_policy.commentID_auto,
					`user`.userTitle
	from policy
	INNER JOIN comment_policy
  on  policy.policyID = comment_policy.comment_policyLabel
  INNER JOIN `comment`
	on comment_policy.commentID_auto = `comment`.commentID_auto
  INNER JOIN `user`
	on `comment`.commentUser = `user`.userID;
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for comparison_H
-- ----------------------------
CREATE DEFINER=`root`@`localhost` PROCEDURE `comparison_H`()
BEGIN
	Select 	policy.parentPolicyID, 
					policyID, 
					comment_policy.commentID_auto,
					`user`.userTitle
	from policy
	INNER JOIN comment_policy
  on  policy.policyID = comment_policy.comment_policyLabel
  INNER JOIN `comment`
	on comment_policy.commentID_auto = `comment`.commentID_auto
  INNER JOIN `user`
	on `comment`.commentUser = `user`.userID
  INNER JOIN afd_category
	on `comment`.AFDID = afd_category.AfDID;
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for comparison_G
-- ----------------------------
CREATE DEFINER=`root`@`localhost` PROCEDURE `comparison_G`()
BEGIN
	Select 	policy.parentPolicyID, 
					policyID, 
					comment_policy.commentID_auto,
					`user`.userTitle
	from policy
	INNER JOIN comment_policy
  on  policy.policyID = comment_policy.comment_policyLabel
  INNER JOIN `comment`
	on comment_policy.commentID_auto = `comment`.commentID_auto
  INNER JOIN `user`
	on `comment`.commentUser = `user`.userID
  INNER JOIN afd_category
	on `comment`.AFDID = afd_category.AfDID
	INNER JOIN category
	on  afd_category.afd_catLabel = category.categoryLabel;
END
;;
DELIMITER ;
