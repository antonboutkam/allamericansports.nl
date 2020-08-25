<?php
class Seo{
    /**
     * Seo::getGeneratedArticle()
     * 
     * @param int $productId
     * @param int $uniqueIdentifier [Kan van alles zijn en zorgt ervoor dat de content uniek is.]
     * @return
     */
    public static function getGeneratedArticle($productId,$uniqueIdentifier){
        $typeId = ProductDao::getProductPropBy('id',$productId,'`type`');
        // Generate article paragraphs
        $sql = sprintf('INSERT IGNORE INTO catalogue_generated_article 
                            (unique_id,fk_catalogue,fk_article_generator,fk_article_generator_section) 
                            SELECT %3$d,%1$d,ag.id,ag.fk_article_generator_section 
                            FROM article_generator ag 
                            WHERE ag.product=%2$d 
                            GROUP BY ag.fk_article_generator_section ORDER BY RAND()',$productId,$typeId,(int)$uniqueIdentifier);
                
        query($sql,__METHOD__);
        $sql = sprintf('SELECT * FROM 
                        article_generator ag,
                        catalogue_generated_article cga
                        WHERE 
                        cga.fk_article_generator=ag.id
                        AND cga.fk_catalogue=%d
                        AND cga.unique_id=%d
                        ORDER BY ag.fk_article_generator_section',$productId,(int)$uniqueIdentifier);
        $articles = fetchArray($sql,__METHOD__);                                
        return $articles;                                     
    }
    public static function getParagraphById($id){
        return fetchRow(sprintf('SELECT * FROM article_generator WHERE id=%d',$id),__METHOD__);        
    }
    public static function getArticleGeneratorSections(){
        return find('article_generator_sections','1=1','section');
    }
    public static function getAllParagraphs(){
        return fetchArray('SELECT                             
                            ags.section section_label,
                            ag.*,
                            pt.type product_type 
                            FROM 
                            article_generator_sections ags,
                            article_generator ag                            
                            LEFT JOIN product_type pt ON pt.id=ag.product
                            WHERE
                            ag.fk_article_generator_section = ags.id
                            ORDER BY pt.type,ags.section,ag.title',__METHOD__);
    }
    
}